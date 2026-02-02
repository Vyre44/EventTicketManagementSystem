<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\CheckInRequest;
use App\Models\Event;
use App\Models\Ticket;
use App\Enums\TicketStatus;
use Illuminate\Support\Facades\DB;

class CheckInController extends Controller
{
    public function showForm(Event $event)
    {
        // event.owner middleware ile yetki kontrolü sağlanacak
        $recent = Ticket::whereHas('ticketType', fn($q) => $q->where('event_id', $event->id))
            ->whereNotNull('checked_in_at')
            ->latest('checked_in_at')
            ->take(10)
            ->get();
        return view('checkin.form', compact('event', 'recent'));
    }

    public function check(CheckInRequest $request, Event $event)
    {
        // event.owner middleware ile yetki kontrolü sağlanacak
        $code = trim($request->validated('code'));
        $result = DB::transaction(function () use ($event, $code) {
            $ticket = Ticket::where('code', $code)
                ->lockForUpdate()
                ->first();
            if (!$ticket) {
                return ['type' => 'error', 'message' => 'Bilet bulunamadı.'];
            }
            $ticketEventId = optional($ticket->ticketType)->event_id;
            if ((int)$ticketEventId !== (int)$event->id) {
                return ['type' => 'error', 'message' => 'Bu bilet bu etkinliğe ait değil.'];
            }
            if (!$ticket->checkIn()) {
                if ($ticket->status === TicketStatus::CHECKED_IN) {
                    $time = $ticket->checked_in_at?->format('d.m.Y H:i');
                    return ['type' => 'warning', 'message' => 'Bu bilet daha önce kullanılmış.' . ($time ? " (".$time.")" : '')];
                }
                return ['type' => 'error', 'message' => 'Bu biletin durumu check-in için uygun değil: ' . $ticket->status->value];
            }
            return ['type' => 'success', 'message' => 'Check-in başarılı!'];
        });

        if ($request->expectsJson() || $request->ajax()) {
            $status = $result['type'] === 'success' ? 200 : ($result['type'] === 'warning' ? 409 : 422);
            return response()->json([
                'type' => $result['type'],
                'message' => $result['message'],
            ], $status);
        }
        return back()->with($result['type'], $result['message']);
    }
}

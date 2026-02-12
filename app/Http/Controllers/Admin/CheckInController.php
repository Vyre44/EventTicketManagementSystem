<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\CheckInRequest;
use App\Models\Event;
use App\Models\Ticket;
use App\Enums\TicketStatus;
use Illuminate\Support\Facades\DB;

/**
 * Admin CheckIn Controller - SÜPER YETKİ
 * 
 * Admin tüm event'lere check-in yapabilir (ownership kontrolü YOK)
 * DB::transaction + lockForUpdate ile double check-in koruması
 * JSON response (AJAX zorunlu)
 * Organizer\CheckInController'dan farklı: ownership bypass
 */
class CheckInController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Check-in form (Admin için - tüm events)
     * 
     * GET /admin/events/{event}/checkin
     * 
     * Route Model Binding: Event
     */
    public function showForm(Event $event)
    {
        // Admin SÜPER YETKİ: ownership kontrolü YOK
        
        $recent = Ticket::whereHas('ticketType', fn($q) => $q->where('event_id', $event->id))
            ->whereNotNull('checked_in_at')
            ->latest('checked_in_at')
            ->take(10)
            ->get();
            
        return view('admin.checkin.form', compact('event', 'recent'));
    }

    /**
     * Check-in işlemi (AJAX - JSON response)
     * 
     * POST /admin/events/{event}/checkin
     * 
     * Request Validation: CheckInRequest
     * code (required|string)
     */
    public function check(CheckInRequest $request, Event $event)
    {
        // Admin SÜPER YETKİ: ownership kontrolü YOK
        
        $code = trim($request->validated('code'));
        
        $result = DB::transaction(function () use ($event, $code) {
            // lockForUpdate: double check-in koruması
            $ticket = Ticket::where('code', $code)
                ->lockForUpdate()
                ->first();
                
            if (!$ticket) {
                return ['success' => false, 'message' => 'Bilet bulunamadı.'];
            }
            
            // Event kontrolü
            $ticketEventId = optional($ticket->ticketType)->event_id;
            if ((int)$ticketEventId !== (int)$event->id) {
                return ['success' => false, 'message' => 'Bu bilet bu etkinliğe ait değil.'];
            }
            
            // Check-in yap
            if (!$ticket->checkIn()) {
                if ($ticket->status === TicketStatus::CHECKED_IN) {
                    $time = $ticket->checked_in_at?->format('d.m.Y H:i');
                    return [
                        'success' => false, 
                        'message' => 'Bu bilet daha önce kullanılmış.' . ($time ? " (".$time.")" : ''), 
                        'warning' => true
                    ];
                }
                return ['success' => false, 'message' => 'Bu biletin durumu check-in için uygun değil: ' . $ticket->status->value];
            }
            
            return ['success' => true, 'message' => 'Check-in başarılı!'];
        });

        // AJAX zorunlu: JSON response
        if ($request->expectsJson() || $request->ajax()) {
            $status = $result['success'] ? 200 : (isset($result['warning']) ? 409 : 422);
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => null
            ], $status);
        }
        
        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}

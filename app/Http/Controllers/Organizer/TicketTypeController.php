<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\StoreTicketTypeRequest;
use App\Http\Requests\Organizer\UpdateTicketTypeRequest;
use App\Models\Event;
use App\Models\TicketType;

class TicketTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,organizer']);
    }

    public function index(Event $event)
    {
        $this->assertEventOwnership($event);

        $ticketTypes = $event->ticketTypes()->latest()->get();

        return view('organizer.ticket_types.index', compact('event', 'ticketTypes'));
    }

    public function create(Event $event)
    {
        $this->assertEventOwnership($event);

        return view('organizer.ticket_types.create', compact('event'));
    }

    public function store(StoreTicketTypeRequest $request, Event $event)
    {
        $this->assertEventOwnership($event);

        $data = $request->validated();
        $data['event_id'] = $event->id;
        $data['total_quantity'] = (int) $data['total_quantity'];
        $data['remaining_quantity'] = (int) $data['total_quantity'];

        $ticketType = TicketType::create($data);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bilet tipi başarıyla oluşturuldu.',
                'data' => [
                    'ticket_type' => $ticketType,
                    'redirect_url' => route('organizer.events.ticket-types.index', $event)
                ]
            ]);
        }

        return redirect()->route('organizer.events.ticket-types.index', $event)
            ->with('success', 'Bilet tipi başarıyla oluşturuldu.');
    }

    public function edit(Event $event, TicketType $ticketType)
    {
        $this->assertEventOwnership($event);
        $this->assertTicketTypeBelongsToEvent($event, $ticketType);

        return view('organizer.ticket_types.edit', compact('event', 'ticketType'));
    }

    public function update(UpdateTicketTypeRequest $request, Event $event, TicketType $ticketType)
    {
        $this->assertEventOwnership($event);
        $this->assertTicketTypeBelongsToEvent($event, $ticketType);

        $data = $request->validated();

        $sold = max(0, (int) $ticketType->total_quantity - (int) $ticketType->remaining_quantity);
        $newTotal = (int) $data['total_quantity'];

        if ($newTotal < $sold) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toplam adet, satılmış bilet adedinden düşük olamaz.',
                    'errors' => [
                        'total_quantity' => ['Toplam adet, satılmış bilet adedinden düşük olamaz.']
                    ]
                ], 422);
            }
            return back()->withErrors([
                'total_quantity' => 'Toplam adet, satılmış bilet adedinden düşük olamaz.'
            ])->withInput();
        }

        $data['remaining_quantity'] = $newTotal - $sold;

        $ticketType->update($data);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bilet tipi başarıyla güncellendi.',
                'data' => [
                    'ticket_type' => $ticketType,
                    'redirect_url' => route('organizer.events.ticket-types.index', $event)
                ]
            ]);
        }

        return redirect()->route('organizer.events.ticket-types.index', $event)
            ->with('success', 'Bilet tipi başarıyla güncellendi.');
    }

    public function destroy(Event $event, TicketType $ticketType)
    {
        $this->assertEventOwnership($event);
        $this->assertTicketTypeBelongsToEvent($event, $ticketType);

        $ticketType->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bilet tipi silindi.',
                'data' => [
                    'redirect_url' => route('organizer.events.ticket-types.index', $event)
                ]
            ]);
        }

        return redirect()->route('organizer.events.ticket-types.index', $event)
            ->with('success', 'Bilet tipi silindi.');
    }

    private function assertEventOwnership(Event $event): void
    {
        $user = auth()->user();

        if ($user && $user->role === \App\Enums\UserRole::ADMIN) {
            return;
        }

        if (!$user || $event->organizer_id !== $user->id) {
            abort(403, 'Bu etkinlik üzerinde işlem yapma yetkiniz yok.');
        }
    }

    private function assertTicketTypeBelongsToEvent(Event $event, TicketType $ticketType): void
    {
        if ((int) $ticketType->event_id !== (int) $event->id) {
            abort(404);
        }
    }
}

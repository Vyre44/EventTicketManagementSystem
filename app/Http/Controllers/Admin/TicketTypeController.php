<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTicketTypeRequest;
use App\Http\Requests\Admin\UpdateTicketTypeRequest;
use App\Models\TicketType;
use App\Models\Event;

class TicketTypeController extends Controller
{
    public function index()
    {
        $q = request('q');
        $eventId = request('event_id');
        $ticketTypes = TicketType::with('event')
            ->when($eventId, fn($q2) => $q2->where('event_id', $eventId))
            ->when($q, fn($q2) => $q2->where('name', 'like', "%$q%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $events = Event::orderByDesc('id')->get(['id', 'title']);
        return view('admin.ticket_types.index', compact('ticketTypes', 'events', 'eventId', 'q'));
    }

    public function show(TicketType $ticketType)
    {
        $ticketType->load('event');
        return view('admin.ticket_types.show', compact('ticketType'));
    }

    public function create()
    {
        $events = Event::orderByDesc('id')->get(['id', 'title']);
        return view('admin.ticket_types.create', compact('events'));
    }

    public function store(StoreTicketTypeRequest $request)
    {
        TicketType::create($request->validated());
        return redirect()->route('admin.ticket-types.index')->with('success', 'TicketType oluşturuldu.');
    }

    public function edit(TicketType $ticketType)
    {
        $events = Event::orderByDesc('id')->get(['id', 'title']);
        return view('admin.ticket_types.edit', compact('ticketType', 'events'));
    }

    public function update(UpdateTicketTypeRequest $request, TicketType $ticketType)
    {
        $ticketType->update($request->validated());
        return redirect()->route('admin.ticket-types.index')->with('success', 'TicketType güncellendi.');
    }

    public function destroy(TicketType $ticketType)
    {
        $ticketType->delete();
        return redirect()->route('admin.ticket-types.index')->with('success', 'TicketType silindi.');
    }
}

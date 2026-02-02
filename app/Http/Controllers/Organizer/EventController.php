<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Http\Requests\Organizer\StoreEventRequest;
use App\Http\Requests\Organizer\UpdateEventRequest;

class EventController extends Controller
{
    // Organizer: Sadece kendi eventlerini listeler
    public function index()
    {
        $events = Event::where('organizer_id', auth()->id())->latest()->paginate(10);
        return view('organizer.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        return view('organizer.events.show', compact('event'));
    }

    public function create()
    {
        return view('organizer.events.create');
    }

    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();
        Event::create($validated + ['organizer_id' => auth()->id()]);
        return redirect()->route('organizer.events.index');
    }

    public function edit(Event $event)
    {
        return view('organizer.events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $validated = $request->validated();
        $event->update($validated);
        return redirect()->route('organizer.events.index');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('organizer.events.index');
    }
}

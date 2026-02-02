<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;

class EventController extends Controller
{
    // Admin: Tüm eventleri listeler
    public function index()
    {
        $events = Event::latest()->paginate(10);
        return view('admin.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        return view('admin.events.show', compact('event'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();
        // organizer_id nullable, admin isterse belirtebilir
        Event::create($validated);
        return redirect()->route('admin.events.index');
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $validated = $request->validated();
        $event->update($validated);
        return redirect()->route('admin.events.index');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events.index');
    }
}

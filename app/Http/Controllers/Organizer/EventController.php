<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Http\Requests\Organizer\StoreEventRequest;
use App\Http\Requests\Organizer\UpdateEventRequest;
use Illuminate\Support\Facades\Storage;

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
        
        // Cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $request->file('cover_image')->store('events', 'public');
        }
        
        Event::create($validated + ['organizer_id' => auth()->id()]);
        return redirect()->route('organizer.events.index')->with('success', 'Etkinlik başarıyla oluşturuldu.');
    }

    public function edit(Event $event)
    {
        return view('organizer.events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $validated = $request->validated();
        
        // Cover image upload
        if ($request->hasFile('cover_image')) {
            // Eski resmi sil
            if ($event->cover_image_path) {
                Storage::disk('public')->delete($event->cover_image_path);
            }
            $validated['cover_image_path'] = $request->file('cover_image')->store('events', 'public');
        }
        
        $event->update($validated);
        return redirect()->route('organizer.events.index')->with('success', 'Etkinlik başarıyla güncellendi.');
    }

    public function destroy(Event $event)
    {
        // Cover image'ı da sil
        if ($event->cover_image_path) {
            Storage::disk('public')->delete($event->cover_image_path);
        }
        
        $event->delete();
        return redirect()->route('organizer.events.index')->with('success', 'Etkinlik başarıyla silindi.');
    }
}

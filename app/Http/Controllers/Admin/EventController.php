<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use Illuminate\Support\Facades\Storage;

/**
 * Admin Etkinlik Controller - Dosya Yüklemeli Resource CRUD
 * 
 * Storage facade: store() ile dosya yükleme (public disk)
 * Form Request'ler: StoreEventRequest, UpdateEventRequest
 * Dosya temizliği: update/destroy'da eski cover_image silinir
 * Admin yetkisi: Tüm etkinlikleri yönetebilir
 */
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
        
        // Cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $request->file('cover_image')->store('events', 'public');
        }
        
        // organizer_id nullable, admin isterse belirtebilir
        Event::create($validated);
        return redirect()->route('admin.events.index')->with('success', 'Etkinlik başarıyla oluşturuldu.');
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
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
        return redirect()->route('admin.events.index')->with('success', 'Etkinlik başarıyla güncellendi.');
    }

    public function destroy(Event $event)
    {
        // Cover image'ı da sil
        if ($event->cover_image_path) {
            Storage::disk('public')->delete($event->cover_image_path);
        }
        
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Etkinlik başarıyla silindi.');
    }
}

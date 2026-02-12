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
    /**
     * Index - Tüm etkinlikleri listele (pagination ile)
     * Admin tüm etkinlikleri görebilir (draft veya published)
     */
    public function index()
    {
        $events = Event::latest()->paginate(10);
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show - Etkinlik detay sayfası
     * Route model binding ile etkinlik otomatik yüklenir
     */
    public function show(Event $event)
    {
        return view('admin.events.show', compact('event'));
    }

    /**
     * Store - Etkinlik kaydetme (FormRequest ile validate)
     * Dosya yüklemesi: public/storage/events klasörüne
     * Admin etkinliği oluşturur (organizer_id auto-fill olmaz)
     * Create - Etkinlik oluşturma formu
     * Organizatör seçimi zorunludur (admin isterse)
     */
    public function create()
    {
        return view('admin.events.create');
    }

    /**
     * Request Validation: StoreEventRequest
     * title, description, location, date, time, status, cover_image (optional)
     */
    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();
        
        // Storage/Dosya Yükleme: Cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $request->file('cover_image')->store('events', 'public');
        }
        
        // organizer_id nullable, admin isterse belirtebilir
        Event::create($validated);
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Etkinlik başarıyla oluşturuldu.']);
        }
        return redirect()->route('admin.events.index')->with('success', 'Etkinlik başarıyla oluşturuldu.');
    }
    // Route Model Binding: Event    
    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Request Validation: UpdateEventRequest
     * title, description, location, date, time, status, cover_image (optional)
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $validated = $request->validated();
        
        // Storage/Dosya Yükleme: Cover image upload + eski dosya silme
        if ($request->hasFile('cover_image')) {
            // Eski resmi sil
            if ($event->cover_image_path) {
                Storage::disk('public')->delete($event->cover_image_path);
            }
            $validated['cover_image_path'] = $request->file('cover_image')->store('events', 'public');
        }
        
    /**
     * Destroy - Etkinlik silme
     * Cascade delete: Events silinirse TicketTypes, Tickets, Orders da silinir (foreign keys)
     * Dosya temizliği: cover_image Storage'dan silinir
     */
        $event->update($validated);
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Etkinlik başarıyla güncellendi.']);
        }
        return redirect()->route('admin.events.index')->with('success', 'Etkinlik başarıyla güncellendi.');
    }
    // Route Model Binding: Event    
    public function destroy(Event $event)
    {
        // Storage/Dosya Yükleme: Cover image silme
        if ($event->cover_image_path) {
            Storage::disk('public')->delete($event->cover_image_path);
        }
        
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Etkinlik başarıyla silindi.');
    }
}

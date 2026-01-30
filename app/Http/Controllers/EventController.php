<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

// EventController: Etkinliklerin CRUD işlemlerini ve yetkilendirmesini yöneten controller.
// - Sadece yetkili kullanıcılar etkinlik oluşturabilir, düzenleyebilir, silebilir.
// - Kapak görseli yükleme, validasyon ve policy ile erişim kontrolü içerir.
class EventController extends Controller
{
    // Controller başlatılırken resource tabanlı yetkilendirme uygulanır.
    // Her action için EventPolicy devreye girer.
    public function __construct()//authorization işlemleri, kod tekrarı önleme
    {
        $this->authorizeResource(Event::class, 'event');
    }

    // Etkinlikleri listele. Sadece kullanıcının görebileceği etkinlikler gelir.
    public function index()
    {
        $events = Event::query()
            ->visibleTo(auth()->user())
            ->latest()
            ->paginate(10);
        return view('events.index', compact('events'));
    }

    // Tek bir etkinliğin detayını göster.
    public function show(Event $event)
    {
        return view('events.show', compact('event'));
    }

    // Etkinlik oluşturma formunu göster.
    public function create()
    {
        return view('events.create');
    }

    // Yeni etkinlik kaydını veritabanına ekle.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'start_time' => ['required','date'],
            'end_time' => ['nullable','date','after_or_equal:start_time'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }

        Event::create([
            'organizer_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'cover_path' => $coverPath,
        ]);

        return redirect()->route('events.index');
    }

    // Etkinlik düzenleme formunu göster.
    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    // Etkinlik kaydını güncelle.
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'start_time' => ['required','date'],
            'end_time' => ['nullable','date','after_or_equal:start_time'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
        ];

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $event->update($data);

        return redirect()->route('events.index');
    }

    // Etkinliği sil.
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index');
    }
}

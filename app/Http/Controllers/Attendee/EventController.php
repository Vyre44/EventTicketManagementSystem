<?php

namespace App\Http\Controllers\Attendee;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

/**
 * EventController (Attendee)
 *
 * Yayınlanmış etkinlikleri listeler ve detaylarını gösterir.
 * Route middleware: auth + role:attendee zorunlu.
 */
class EventController extends Controller
{
    /**
     * Tüm yayınlanmış etkinlikleri listele
     */
    public function index(Request $request)
    {
        $query = Event::where('status', EventStatus::PUBLISHED)
            ->with(['ticketTypes' => function($query) {
                $query->where('remaining_quantity', '>', 0);
            }])
            ->orderBy('start_time');

        // Arama filtresi
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($query) use ($q) {
                    $query->where('title', 'ilike', "%{$q}%")
                        ->orWhere('description', 'ilike', "%{$q}%");
            });
        }

        $events = $query->paginate(12);

        return view('attendee.events.index', compact('events'));
    }

    /**
     * Etkinlik detay sayfası - bilet tipleri ile birlikte
     */
    public function show(Event $event)
    {
        // Sadece yayınlanmış etkinlikler görülebilir
        if ($event->status !== EventStatus::PUBLISHED) {
            abort(404, 'Etkinlik bulunamadı veya henüz yayınlanmadı.');
        }

        $event->load([
            'ticketTypes' => function($query) {
                $query->where('remaining_quantity', '>', 0)
                      ->orderBy('price');
            },
            'organizer:id,name,email'
        ]);

        return view('attendee.events.show', compact('event'));
    }
}

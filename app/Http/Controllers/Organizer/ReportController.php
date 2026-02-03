<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;

/**
 * ReportController (Organizer)
 * 
 * Organizer raporları - SADECE kendi event'lerine ait data
 */
class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,organizer']);
    }

    /**
     * Event ticket raporu
     * 
     * GET /organizer/reports/events/{event}/tickets
     */
    public function eventTickets(Request $request, Event $event)
    {
        $user = auth()->user();

        // Organizer kendi event'ini görebilir, admin heryerden görebilir
        if (!$user->isAdmin() && $event->organizer_id !== $user->id) {
            abort(403, 'Bu etkinliğin raporunu görüntüleme yetkiniz yok.');
        }

        $query = Ticket::whereHas('ticketType', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })->with(['ticketType', 'order.user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search by ID or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('code', 'like', "%$search%")
                  ->orWhereHas('order.user', function ($subq) use ($search) {
                      $subq->where('email', 'like', "%$search%");
                  });
            });
        }

        $tickets = $query->latest()->paginate($request->input('per_page', 20));

        return view('organizer.reports.event_tickets', compact('event', 'tickets'));
    }

    /**
     * Event ticket raporu CSV export
     * 
     * GET /organizer/reports/events/{event}/tickets/export
     */
    public function exportEventTickets(Request $request, Event $event)
    {
        $user = auth()->user();

        // Organizer kendi event'ini export edebilir, admin her yerde
        if (!$user->isAdmin() && $event->organizer_id !== $user->id) {
            abort(403, 'Bu etkinliğin raporunu indirme yetkiniz yok.');
        }

        $query = Ticket::whereHas('ticketType', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })->with(['ticketType', 'order.user']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('code', 'like', "%$search%")
                  ->orWhereHas('order.user', function ($subq) use ($search) {
                      $subq->where('email', 'like', "%$search%");
                  });
            });
        }

        $tickets = $query->get();

        return $this->buildCsvResponse($event, $tickets);
    }

    /**
     * Native CSV response builder
     */
    private function buildCsvResponse(Event $event, $tickets)
    {
        $filename = "tickets_{$event->id}_" . now()->format('Y-m-d_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        // CSV content
        $content = fopen('php://memory', 'r+');

        // Header
        fputcsv($content, [
            'ticket_id',
            'event_title',
            'ticket_type',
            'ticket_status',
            'checked_in_at',
            'user_email',
            'order_status'
        ]);

        // Rows
        foreach ($tickets as $ticket) {
            fputcsv($content, [
                $ticket->id,
                $event->title,
                $ticket->ticketType->name,
                $ticket->status->value,
                $ticket->checked_in_at?->format('Y-m-d H:i:s') ?? '',
                $ticket->order?->user?->email ?? 'N/A',
                $ticket->order?->status?->value ?? 'N/A',
            ]);
        }

        rewind($content);
        $csv = stream_get_contents($content);
        fclose($content);

        return response($csv, 200, $headers);
    }
}

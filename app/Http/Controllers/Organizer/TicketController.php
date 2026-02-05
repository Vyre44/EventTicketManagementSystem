<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * TicketController (Organizer)
 *
 * Organizer kendi event'lerine ait tickets'ları görebilir ve yönetebilir.
 * Admin tüm tickets'ları görebilir.
 */
class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,organizer']);
    }

    /**
     * Organizer/Admin için tickets listesi
     *
     * GET /organizer/tickets
     */
    public function index()
    {
        $user = auth()->user();

        // Admin tüm tickets'ları görebilir, organizer sadece kendi event'lerininkini
        $tickets = Ticket::with(['ticketType.event:id,title,organizer_id', 'order.user:id,name,email'])
            ->when(!$user->isAdmin(), function (Builder $query) use ($user) {
                $query->whereHas('ticketType.event', function (Builder $subquery) use ($user) {
                    $subquery->where('organizer_id', $user->id);
                });
            })
            ->latest()
            ->paginate(20);

        return view('organizer.tickets.index', compact('tickets'));
    }

    /**
     * Organizer/Admin için ticket detayı
     *
     * GET /organizer/tickets/{ticket}
     */
    public function show(Ticket $ticket)
    {
        $user = auth()->user();

        // Ownership kontrolü (Admin bypass yapabilir)
        if (!$user->isAdmin() && $ticket->ticketType->event->organizer_id !== $user->id) {
            abort(403, 'Bu bilet bilgisini görüntüleme yetkiniz yok.');
        }

        $ticket->load(['ticketType.event', 'order.user:id,name,email']);

        return view('organizer.tickets.show', compact('ticket'));
    }

    /**
     * Check-in'i geri al (CHECKED_IN → ACTIVE)
     *
     * POST /organizer/tickets/{ticket}/checkin-undo
     */
    public function checkinUndo(Ticket $ticket)
    {
        $user = auth()->user();

        // Ownership kontrolü (Admin bypass yapabilir)
        if (!$user->isAdmin() && $ticket->ticketType->event->organizer_id !== $user->id) {
            $message = 'Bu bilet üzerinde işlem yapma yetkiniz yok.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null
                ], 403);
            }
            return back()->with('error', $message);
        }

        // Sadece CHECKED_IN durumundaki biletler geri alınabilir
        if ($ticket->status !== TicketStatus::CHECKED_IN) {
            $message = 'Bu bilet check-in geri alınamaz. Sadece kullanılan biletler geri alınabilir.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null
                ], 422);
            }
            return back()->with('error', $message);
        }

        // Check-in'i geri al
        $ticket->update([
            'status' => TicketStatus::ACTIVE,
            'checked_in_at' => null,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bilet check-in\'i başarıyla geri alındı.',
                'data' => ['ticket' => $ticket]
            ]);
        }

        return back()->with('success', 'Bilet check-in\'i başarıyla geri alındı.');
    }

    /**
     * Bileti iptal et (ACTIVE → CANCELLED)
     *
     * POST /organizer/tickets/{ticket}/cancel
     */
    public function cancel(Ticket $ticket)
    {
        $user = auth()->user();

        // Ownership kontrolü (Admin bypass yapabilir)
        if (!$user->isAdmin() && $ticket->ticketType->event->organizer_id !== $user->id) {
            $message = 'Bu bilet üzerinde işlem yapma yetkiniz yok.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null
                ], 403);
            }
            return back()->with('error', $message);
        }

        // Sadece ACTIVE durumundaki biletler iptal edilebilir
        if ($ticket->status !== TicketStatus::ACTIVE) {
            $message = 'Bu bilet iptal edilemez. Sadece aktif biletler iptal edilebilir.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null
                ], 422);
            }
            return back()->with('error', $message);
        }

        // Bileti iptal et + stok iadesi (idempotent + concurrency safe)
        DB::transaction(function () use (&$ticket) {
            // Ticket'i lock ile çek ve status kontrol et (idempotency guard)
            $ticket = Ticket::lockForUpdate()->findOrFail($ticket->id);
            
            // Zaten CANCELLED veya REFUNDED ise tekrar işlem yapma
            if (in_array($ticket->status, [TicketStatus::CANCELLED, TicketStatus::REFUNDED], true)) {
                return;
            }
            
            // Status'u CANCELLED yap
            $ticket->update(['status' => TicketStatus::CANCELLED]);
            
            // Stok iade et
            $ticketType = TicketType::lockForUpdate()->findOrFail($ticket->ticket_type_id);
            $ticketType->increment('remaining_quantity');
        });

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bilet başarıyla iptal edildi.',
                'data' => ['ticket' => $ticket->fresh()]
            ]);
        }

        return back()->with('success', 'Bilet başarıyla iptal edildi.');
    }

    /**
     * Check-in yap (ACTIVE → CHECKED_IN)
     *
     * POST /organizer/tickets/{ticket}/checkin
     */
    public function checkin(Ticket $ticket)
    {
        $user = auth()->user();

        // Ownership kontrolü (Admin bypass yapabilir)
        if (!$user->isAdmin() && $ticket->ticketType->event->organizer_id !== $user->id) {
            $message = 'Bu bilet üzerinde işlem yapma yetkiniz yok.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null
                ], 403);
            }
            return back()->with('error', $message);
        }

        // Sadece ACTIVE durumundaki biletler check-in yapılabilir
        if ($ticket->status !== TicketStatus::ACTIVE) {
            $message = 'Bu bilet check-in yapılamaz. Sadece aktif biletlere check-in yapılabilir.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null
                ], 422);
            }
            return back()->with('error', $message);
        }

        // Check-in yap
        if (!$ticket->checkIn()) {
            $message = 'Check-in başarısız oldu.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null
                ], 422);
            }
            return back()->with('error', $message);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bilet başarıyla check-in yapıldı.',
                'data' => ['ticket' => $ticket]
            ]);
        }

        return back()->with('success', 'Bilet başarıyla check-in yapıldı.');
    }
}

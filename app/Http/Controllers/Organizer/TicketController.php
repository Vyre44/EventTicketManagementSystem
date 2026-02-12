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

        $query = Ticket::with(['ticketType.event:id,title,organizer_id', 'order.user:id,name,email'])
            ->when(!$user->isAdmin(), function (Builder $query) use ($user) {
                $query->whereHas('ticketType.event', function (Builder $subquery) use ($user) {
                    $subquery->where('organizer_id', $user->id);
                });
            });

        // Filter by status
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        // Filter by event name (title)
        if (request()->filled('event_search')) {
            $search = request('event_search');
            $query->whereHas('ticketType.event', function (Builder $q) use ($search) {
                $q->where('title', 'like', "%$search%");
            });
        }

        // Filter by code or email search
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function (Builder $q) use ($search) {
                $q->where('code', 'like', "%$search%")
                  ->orWhereHas('order.user', function (Builder $subq) use ($search) {
                      $subq->where('email', 'like', "%$search%");
                  });
            });
        }

        $tickets = $query->latest()
            ->paginate(20)
            ->withQueryString();

        $statuses = TicketStatus::cases();

        return view('organizer.tickets.index', compact('tickets', 'statuses'));
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

        // Transaction + lockForUpdate ile race condition engelini
        $result = DB::transaction(function () use ($ticket) {
            // Ticket'i lock ile çek
            $freshTicket = Ticket::lockForUpdate()->findOrFail($ticket->id);
            
            // Sadece CHECKED_IN durumundaki biletler geri alınabilir
            if ($freshTicket->status !== TicketStatus::CHECKED_IN) {
                return [
                    'success' => false,
                    'message' => 'Bu bilet check-in geri alınamaz. Sadece kullanılan biletler geri alınabilir.',
                    'status' => 422
                ];
            }
            
            // Check-in'i geri al (CHECKED_IN → ACTIVE)
            if (!$freshTicket->undoCheckIn()) {
                return [
                    'success' => false,
                    'message' => 'Check-in geri alma başarısız oldu.',
                    'status' => 422
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Bilet check-in\'i başarıyla geri alındı.',
                'status' => 200,
                'ticket' => $freshTicket->fresh()
            ];
        });

        if (request()->expectsJson()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['ticket'] ?? null
            ], $result['status']);
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
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

        // Order status kontrolü - sadece PAID order'lar check-in yapılabilir
        $order = $ticket->order;
        
        if (!$order || $order->status !== \App\Enums\OrderStatus::PAID) {
            $message = 'Bu bilet için ödeme tamamlanmamış.';
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null
                ], 403);
            }
            
            return back()->with('error', $message);
        }

        // Transaction + lockForUpdate ile race condition engelini
        $result = DB::transaction(function () use ($ticket) {
            // Ticket'i lock ile çek (double check-in koruması)
            $freshTicket = Ticket::lockForUpdate()->findOrFail($ticket->id);
            
            // Sadece ACTIVE durumundaki biletler check-in yapılabilir
            if ($freshTicket->status !== TicketStatus::ACTIVE) {
                $message = 'Bu bilet check-in yapılamaz. Durum: ' . $freshTicket->status->value;
                if ($freshTicket->status === TicketStatus::CHECKED_IN) {
                    $time = $freshTicket->checked_in_at?->format('d.m.Y H:i');
                    $message = 'Bu bilet daha önce kullanılmış.' . ($time ? " (".$time.")" : '');
                }
                return [
                    'success' => false,
                    'message' => $message,
                    'status' => 422
                ];
            }
            
            // Check-in yap
            if (!$freshTicket->checkIn()) {
                return [
                    'success' => false,
                    'message' => 'Check-in başarısız oldu.',
                    'status' => 422
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Bilet başarıyla check-in yapıldı.',
                'status' => 200,
                'ticket' => $freshTicket->fresh()
            ];
        });

        if (request()->expectsJson()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['ticket'] ?? null
            ], $result['status']);
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}

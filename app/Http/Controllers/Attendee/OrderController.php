<?php

namespace App\Http\Controllers\Attendee;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendee\BuyTicketRequest;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * OrderController (Attendee)
 *
 * Satın alma akışı:
 * 1. POST /events/{event}/buy → Order(PENDING) + Ticket(ACTIVE) + quota düş
 * 2. GET  /orders/{order}      → Ödeme sayfası (PENDING) / bilet listesi (diğerleri)
 * 3. POST /orders/{order}/pay  → Order(PAID) işaretleme
 * 4. GET  /orders              → Kullanıcının siparişleri
 */
class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:attendee']);
    }

    /**
    * STEP 1: "Satın Al" → Order(PENDING) + Ticket(ACTIVE) + kota düş
     *
     * POST /events/{event}/buy
     * 
     * Request Validation: BuyTicketRequest
     * tickets (array), tickets.*.ticket_type_id, tickets.*.quantity
     */
    public function buy(BuyTicketRequest $request, Event $event)
    {
        // Etkinlik yayınlanmış mı?
        if ($event->status !== EventStatus::PUBLISHED) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu etkinlik için bilet satışı yapılmamaktadır.'
                ], 422);
            }
            return back()->withErrors(['event' => 'Bu etkinlik için bilet satışı yapılmamaktadır.']);
        }

        // Etkinlik tarihi geçmişse satın almayı engelle
        if ($event->start_time && $event->start_time->isPast()) {
            $message = 'Etkinlik tarihi geçmiş.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            return back()->withErrors(['event' => $message]);
        }

        $ticketTypeQuantities = $request->ticket_types; // ['ticket_type_id' => quantity]

        try {
            $order = DB::transaction(function () use ($event, $ticketTypeQuantities) {
                $totalAmount = 0;

                // Kota kontrolü + toplam tutar hesaplama
                foreach ($ticketTypeQuantities as $ticketTypeId => $quantity) {
                    $ticketType = TicketType::lockForUpdate()->findOrFail($ticketTypeId);

                    if ($ticketType->event_id !== $event->id) {
                        throw new \InvalidArgumentException('Geçersiz bilet tipi.');
                    }

                    // Sale window kontrolü
                    if ($ticketType->sale_start && now()->isBefore($ticketType->sale_start)) {
                        throw new \InvalidArgumentException("{$ticketType->name} için satış henüz başlamadı.");
                    }
                    
                    if ($ticketType->sale_end && now()->isAfter($ticketType->sale_end)) {
                        throw new \InvalidArgumentException("{$ticketType->name} için satış süresi doldu.");
                    }

                    if ($ticketType->remaining_quantity < $quantity) {
                        throw new \InvalidArgumentException("{$ticketType->name} için yeterli kota yok. Mevcut: {$ticketType->remaining_quantity}");
                    }

                    $totalAmount += $ticketType->price * $quantity;
                }

                $order = Order::create([
                    'user_id' => auth()->id(),
                    'event_id' => $event->id,
                    'total_amount' => $totalAmount,
                    'status' => OrderStatus::PENDING,
                ]);

                foreach ($ticketTypeQuantities as $ticketTypeId => $quantity) {
                    $ticketType = TicketType::lockForUpdate()->findOrFail($ticketTypeId);

                    for ($i = 0; $i < $quantity; $i++) {
                        $order->tickets()->create([
                            'ticket_type_id' => $ticketTypeId,
                            'code' => $this->generateUniqueTicketCode(),
                            'status' => TicketStatus::ACTIVE,
                        ]);
                    }

                    $ticketType->decrement('remaining_quantity', $quantity);
                }

                return $order;
            });
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return back()->withErrors(['ticket_types' => $e->getMessage()]);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'İşlem sırasında bir hata oluştu.'
                ], 500);
            }
            return back()->withErrors(['order' => 'İşlem sırasında bir hata oluştu.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Siparişiniz oluşturuldu. Lütfen ödemeyi tamamlayın.',
                'data' => [
                    'order_id' => $order->id,
                    'redirect_url' => route('attendee.orders.show', $order)
                ]
            ]);
        }

        return redirect()->route('attendee.orders.show', $order)
            ->with('success', 'Siparişiniz oluşturuldu. Lütfen ödemeyi tamamlayın.');
    }

    /**
     * STEP 2: Sipariş detayı (PENDING ise ödeme sayfası, PAID ise bilet listesi)
     *
     * GET /orders/{order}
     */
    public function show(Order $order)
    {
        // Kullanıcı kendi siparişini görebilir
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Bu siparişi görüntüleme yetkiniz yok.');
        }

        $order->load(['event', 'tickets.ticketType']);

        // Sipariş PENDING ise → Ödeme sayfası
        if ($order->status === OrderStatus::PENDING) {
            if ($order->tickets->isEmpty()) {
                return redirect()->route('attendee.orders.index')
                    ->withErrors(['order' => 'Sipariş bilgisi bulunamadı.']);
            }

            $ticketTypeQuantities = $order->tickets
                ->groupBy('ticket_type_id')
                ->map(fn($group) => $group->count())
                ->all();

            $ticketTypes = $order->tickets
                ->mapWithKeys(fn($ticket) => [$ticket->ticket_type_id => $ticket->ticketType]);

            return view('attendee.orders.checkout', compact('order', 'ticketTypeQuantities', 'ticketTypes'));
        }

        // Sipariş PAID/CANCELLED/REFUNDED ise → Bilet listesi
        return view('attendee.orders.show', compact('order'));
    }

    /**
    * STEP 3: Ödemeyi tamamla → Order(PAID) işaretleme
     *
     * POST /orders/{order}/pay
     */
    public function pay(Order $order)
    {
        // Kullanıcı kendi siparişini ödeyebilir
        if ($order->user_id !== auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu siparişi ödeme yetkiniz yok.'
                ], 403);
            }
            abort(403, 'Bu siparişi ödeme yetkiniz yok.');
        }

        // Sipariş zaten PAID mi?
        if ($order->status !== OrderStatus::PENDING) {
            $message = 'Bu sipariş zaten işlenmiş.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            return redirect()->route('attendee.orders.show', $order)
                ->withErrors(['order' => $message]);
        }

        if ($order->tickets()->count() === 0) {
            $message = 'Sipariş biletleri bulunamadı.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            return redirect()->route('attendee.orders.index')
                ->withErrors(['order' => $message]);
        }

        try {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => OrderStatus::PAID,
                    'paid_at' => now(),
                ]);
            });

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ödeme başarılı! Biletleriniz hazır.',
                    'data' => [
                        'order' => $order->load('tickets.ticketType')
                    ]
                ]);
            }

            return redirect()->route('attendee.orders.show', $order)
                ->with('success', 'Ödeme başarılı! Biletleriniz hazır.');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }

    /**
     * STEP 4: Kullanıcının tüm siparişleri
     *
     * GET /orders
     */
    public function index()
    {
        $orders = auth()->user()->orders()
            ->with('event:id,title,start_time')
            ->withCount('tickets')
            ->latest()
            ->paginate(10);

        return view('attendee.orders.index', compact('orders'));
    }

    /**
     * Siparişi iptal et (PENDING → CANCELLED)
     *
     * POST /orders/{order}/cancel
     */
    public function cancel(Order $order)
    {
        // Kullanıcı kendi siparişini iptal edebilir
        if ($order->user_id !== auth()->id()) {
            $message = 'Bu siparişi iptal etme yetkiniz yok.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 403);
            }
            return back()->with('error', $message);
        }

        // Sadece PENDING siparişler iptal edilebilir
        if ($order->status !== OrderStatus::PENDING) {
            $message = 'Bu sipariş iptal edilemez. Sadece bekleyen siparişler iptal edilebilir.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            return back()->with('error', $message);
        }

        // Order'ı iptal et ve biletleri CANCELLED statüsüne çek (audit trail korunur)
        $cancelled = false;
        DB::transaction(function () use (&$order, &$cancelled) {
            // Order'ı lock ile çek ve status kontrol et (idempotency guard)
            $order = Order::lockForUpdate()->findOrFail($order->id);
            
            // Zaten CANCELLED veya REFUNDED ise tekrar işlem yapma
            if (in_array($order->status, [OrderStatus::CANCELLED, OrderStatus::REFUNDED], true)) {
                $cancelled = false;
                return;
            }
            
            $tickets = $order->tickets()->with('ticketType')->get();

            foreach ($tickets as $ticket) {
                // Ticket'i lock ile çek (concurrency protection)
                $ticketLocked = Ticket::lockForUpdate()->findOrFail($ticket->id);
                
                // Zaten CANCELLED veya REFUNDED ise skip (idempotency guard)
                if (in_array($ticketLocked->status, [TicketStatus::CANCELLED, TicketStatus::REFUNDED], true)) {
                    continue;
                }
                
                // Ticket status'unu CANCELLED'a çek (DELETE etme - audit trail korunmalı)
                $ticketLocked->update(['status' => TicketStatus::CANCELLED]);

                // Stock iadesi yap
                $ticketType = TicketType::lockForUpdate()->findOrFail($ticketLocked->ticket_type_id);
                $ticketType->increment('remaining_quantity');
            }

            $order->update([
                'status' => OrderStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);
            $cancelled = true;
        });

        // Refresh order model to get updated status
        $order->refresh();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sipariş başarıyla iptal edildi.',
                'data' => ['order' => $order]
            ]);
        }

        return redirect()->route('attendee.orders.index')
            ->with('success', 'Sipariş başarıyla iptal edildi.');
    }

    /**
     * Siparişi geri al (PAID → REFUNDED)
     * Transaction içinde tickets'i refund et, remaining_quantity geri artır
     *
     * POST /orders/{order}/refund
     */
    public function refund(Order $order)
    {
        // Kullanıcı kendi siparişini geri alabilir
        if ($order->user_id !== auth()->id()) {
            $message = 'Bu siparişi geri alma yetkiniz yok.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 403);
            }
            return back()->with('error', $message);
        }

        // Sadece PAID siparişler geri alınabilir
        if ($order->status !== OrderStatus::PAID) {
            $message = 'Bu sipariş geri alınamaz. Sadece ödenmiş siparişler geri alınabilir.';
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            return back()->with('error', $message);
        }

        try {
            DB::transaction(function () use (&$order) {
                // Order'ı lock ile çek ve status kontrol et (idempotency guard)
                $order = Order::lockForUpdate()->findOrFail($order->id);
                
                // Zaten REFUNDED veya CANCELLED ise tekrar işlem yapma
                if (in_array($order->status, [OrderStatus::REFUNDED, OrderStatus::CANCELLED], true)) {
                    return;
                }
                
                // Order'a bağlı tickets'i yükle
                $tickets = $order->tickets()->with('ticketType')->get();

                // Her ticket için status'u refunded yap ve remaining_quantity'yi geri artır
                foreach ($tickets as $ticket) {
                    // Ticket'i lock ile çek (concurrency protection)
                    $ticketLocked = Ticket::lockForUpdate()->findOrFail($ticket->id);
                    
                    // Zaten REFUNDED veya CANCELLED ise skip (idempotency guard)
                    if (in_array($ticketLocked->status, [TicketStatus::REFUNDED, TicketStatus::CANCELLED], true)) {
                        continue;
                    }
                    
                    // Ticket status'unu refund'a çek
                    $ticketLocked->update(['status' => TicketStatus::REFUNDED]);

                    // TicketType'ın remaining_quantity'sini kilitle ve artır
                    $ticketType = TicketType::lockForUpdate()->findOrFail($ticketLocked->ticket_type_id);
                    $ticketType->increment('remaining_quantity');
                }

                // Order'ı refund'a çek
                $order->update([
                    'status' => OrderStatus::REFUNDED,
                    'refunded_at' => now(),
                ]);
            });

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sipariş başarıyla geri alındı. Ödemeniz iade edilecektir.',
                    'data' => ['order' => $order->load('tickets.ticketType')]
                ]);
            }

            return redirect()->route('attendee.orders.show', $order)
                ->with('success', 'Sipariş başarıyla geri alındı. Ödemeniz iade edilecektir.');

        } catch (\Exception $e) {
            $message = 'İade işlemi sırasında hata oluştu: ' . $e->getMessage();
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            return back()->with('error', $message);
        }
    }

    /**
     * Benzersiz bilet kodu üret
     */
    private function generateUniqueTicketCode(): string
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (\App\Models\Ticket::where('code', $code)->exists());

        return $code;
    }
}

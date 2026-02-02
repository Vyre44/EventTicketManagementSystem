<?php

namespace App\Http\Controllers\Attendee;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendee\BuyTicketRequest;
use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * OrderController (Attendee)
 *
 * Satın alma akışı (Model 1A):
 * 1. POST /events/{event}/buy → Order(PENDING) oluştur
 * 2. GET  /orders/{order}      → Ödeme sayfası göster (status=PENDING ise)
 * 3. POST /orders/{order}/pay  → Order(PAID) + Ticket(ACTIVE) oluştur + quota düş
 * 4. GET  /orders              → Kullanıcının siparişleri
 */
class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * STEP 1: "Satın Al" butonuna basınca Order(PENDING) oluştur
     *
     * POST /events/{event}/buy
     */
    public function buy(BuyTicketRequest $request, Event $event)
    {
        // Etkinlik yayınlanmış mı?
        if ($event->status !== EventStatus::PUBLISHED) {
            return back()->withErrors(['event' => 'Bu etkinlik için bilet satışı yapılmamaktadır.']);
        }

        $ticketTypeQuantities = $request->ticket_types; // ['ticket_type_id' => quantity]
        $totalAmount = 0;

        // Kota kontrolü + toplam tutar hesaplama
        foreach ($ticketTypeQuantities as $ticketTypeId => $quantity) {
            $ticketType = TicketType::lockForUpdate()->findOrFail($ticketTypeId);

            // Bilet tipi bu etkinliğe ait mi?
            if ($ticketType->event_id !== $event->id) {
                return back()->withErrors(['ticket_types' => 'Geçersiz bilet tipi.']);
            }

            // Yeterli kota var mı?
            if ($ticketType->remaining_quantity < $quantity) {
                return back()->withErrors([
                    'ticket_types' => "{$ticketType->name} için yeterli kota yok. Mevcut: {$ticketType->remaining_quantity}"
                ]);
            }

            $totalAmount += $ticketType->price * $quantity;
        }

        // Order(PENDING) oluştur - henüz Ticket YOK
        $order = Order::create([
            'user_id' => auth()->id(),
            'event_id' => $event->id,
            'total_amount' => $totalAmount,
            'status' => OrderStatus::PENDING,
        ]);

        // Session'a ticket_types kaydet (ödeme tamamlanınca kullanılacak)
        session([
            "order_{$order->id}_ticket_types" => $ticketTypeQuantities,
        ]);

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

        $order->load('event');

        // Sipariş PENDING ise → Ödeme sayfası
        if ($order->status === OrderStatus::PENDING) {
            $ticketTypeQuantities = session("order_{$order->id}_ticket_types", []);

            // Session'da veri yoksa hata
            if (empty($ticketTypeQuantities)) {
                return redirect()->route('attendee.orders.index')
                    ->withErrors(['order' => 'Sipariş bilgisi bulunamadı.']);
            }

            // Bilet tipi detaylarını çek
            $ticketTypes = TicketType::whereIn('id', array_keys($ticketTypeQuantities))
                ->get()
                ->keyBy('id');

            return view('attendee.orders.checkout', compact('order', 'ticketTypeQuantities', 'ticketTypes'));
        }

        // Sipariş PAID/CANCELLED/REFUNDED ise → Bilet listesi
        $order->load('tickets.ticketType');

        return view('attendee.orders.show', compact('order'));
    }

    /**
     * STEP 3: Ödemeyi tamamla → Order(PAID) + Ticket(ACTIVE) oluştur + quota düş
     *
     * POST /orders/{order}/pay
     */
    public function pay(Order $order)
    {
        // Kullanıcı kendi siparişini ödeyebilir
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Bu siparişi ödeme yetkiniz yok.');
        }

        // Sipariş zaten PAID mi?
        if ($order->status !== OrderStatus::PENDING) {
            return redirect()->route('attendee.orders.show', $order)
                ->withErrors(['order' => 'Bu sipariş zaten işlenmiş.']);
        }

        $ticketTypeQuantities = session("order_{$order->id}_ticket_types", []);

        // Session'da veri yoksa hata
        if (empty($ticketTypeQuantities)) {
            return redirect()->route('attendee.orders.index')
                ->withErrors(['order' => 'Sipariş bilgisi bulunamadı.']);
        }

        try {
            DB::transaction(function () use ($order, $ticketTypeQuantities) {
                // Her bilet tipi için Ticket oluştur ve kotayı azalt
                foreach ($ticketTypeQuantities as $ticketTypeId => $quantity) {
                    $ticketType = TicketType::lockForUpdate()->findOrFail($ticketTypeId);

                    // Son kontrol: Hala yeterli kota var mı?
                    if ($ticketType->remaining_quantity < $quantity) {
                        throw new \Exception("{$ticketType->name} için yeterli kota kalmadı. Mevcut: {$ticketType->remaining_quantity}");
                    }

                    // Ticket(ACTIVE) oluştur
                    for ($i = 0; $i < $quantity; $i++) {
                        $order->tickets()->create([
                            'ticket_type_id' => $ticketTypeId,
                            'code' => $this->generateUniqueTicketCode(),
                            'status' => TicketStatus::ACTIVE,
                        ]);
                    }

                    // Kotayı azalt
                    $ticketType->decrement('remaining_quantity', $quantity);
                }

                // Order(PAID) yap
                $order->update([
                    'status' => OrderStatus::PAID,
                    'paid_at' => now(),
                ]);
            });

            // Session temizle
            session()->forget("order_{$order->id}_ticket_types");

            return redirect()->route('attendee.orders.show', $order)
                ->with('success', 'Ödeme başarılı! Biletleriniz hazır.');

        } catch (\Exception $e) {
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
            return back()->with('error', 'Bu siparişi iptal etme yetkiniz yok.');
        }

        // Sadece PENDING siparişler iptal edilebilir
        if ($order->status !== OrderStatus::PENDING) {
            return back()->with('error', 'Bu sipariş iptal edilemez. Sadece bekleyen siparişler iptal edilebilir.');
        }

        // Order'ı iptal et
        $order->update(['status' => OrderStatus::CANCELLED]);

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
            return back()->with('error', 'Bu siparişi geri alma yetkiniz yok.');
        }

        // Sadece PAID siparişler geri alınabilir
        if ($order->status !== OrderStatus::PAID) {
            return back()->with('error', 'Bu sipariş geri alınamaz. Sadece ödenmemiş siparişler geri alınabilir.');
        }

        try {
            DB::transaction(function () use ($order) {
                // Order'a bağlı tickets'i yükle
                $tickets = $order->tickets()->with('ticketType')->get();

                // Her ticket için status'u refunded yap ve remaining_quantity'yi geri artır
                foreach ($tickets as $ticket) {
                    // Ticket status'unu refund'a çek
                    $ticket->update(['status' => TicketStatus::REFUNDED]);

                    // TicketType'ın remaining_quantity'sini kilitle ve artır
                    $ticketType = TicketType::lockForUpdate()->findOrFail($ticket->ticket_type_id);
                    $ticketType->increment('remaining_quantity');
                }

                // Order'ı refund'a çek
                $order->update([
                    'status' => OrderStatus::REFUNDED,
                    'refunded_at' => now(),
                ]);
            });

            return redirect()->route('attendee.orders.show', $order)
                ->with('success', 'Sipariş başarıyla geri alındı. Ödemeniz iade edilecektir.');

        } catch (\Exception $e) {
            return back()->with('error', 'İade işlemi sırasında hata oluştu: ' . $e->getMessage());
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

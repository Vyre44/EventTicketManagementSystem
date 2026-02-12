<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTicketRequest;
use App\Http\Requests\Admin\UpdateTicketRequest;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Ticket Controller - Resource Controller
 * 
 * CRUD operations: index, show, create, store, edit, update, destroy
 * AJAX actions: checkin, checkinUndo, cancelTicket (JSON response)
 * Eager loading: with(['order.user', 'ticketType.event'])
 * Filtering: whereHas() for nested relationships
 */
class TicketController extends Controller
{
    /**
     * Index - Eager loading with(['order.user', 'ticketType.event'])
     * Query filters: q (search), status, event_search (title), user_email
     * whereHas() for nested relationship filtering
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['order.user', 'ticketType.event']);

        // Search by code or ID
        if ($q = $request->input('q')) {
            $query->where('code', 'like', "%$q%")
                  ->orWhere('id', $q);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by event title (text search)
        if ($request->filled('event_search')) {
            $query->whereHas('ticketType.event', function($q) {
                $q->where('title', 'like', '%' . request('event_search') . '%');
            });
        }

        // Filter by user email (nested relationship)
        if ($request->filled('user_email')) {
            $query->whereHas('order.user', function($q) {
                $q->where('email', 'like', '%' . request('user_email') . '%');
            });
        }

        // Paginate with query string preservation
        $tickets = $query->latest()->paginate(20)->withQueryString();
        
        /**
         * ADIM 7: STATUS ENUM'INı VIEW'E GÖNDER
         * 
         * Filter dropdown'u için tüm status'ları al
         * TicketStatus::cases() -> [ACTIVE, CHECKED_IN, CANCELLED, REFUNDED]
         */
        $statuses = TicketStatus::cases();

        return view('admin.tickets.index', compact('tickets', 'statuses'));
    }

    /**
     * ============================================================
     * BİLET DETAYI - SHOW
     * ============================================================
     * 
     * ROUTE:
     * GET /admin/tickets/{id}
     * 
     * AÇIKLAMA:
     * Belirli bir bilet'in detaylı bilgilerini göster
     * 
     * PARAMETRELER:
     * @param Ticket $ticket - Route model binding ile otomatik inject
     * 
     * ROUTE MODEL BINDING NEDİR?
     * - /admin/tickets/5 -> Ticket::find(5) otomatik yapılır
     * - Bilet bulunamazsa 404 hatası
     * - Controller'ın ilk satırında Ticket $ticket yazınca çalışır
     * 
     * RETURN:
     * View: admin.tickets.show
     * Data: $ticket (with relations)
     */
    public function show(Ticket $ticket)
    {
        /**
         * ADIM 1: İLİŞKİLERİ YÜKLE
         * 
         * load(): Eager loading (query'den sonra)
         * 
         * FARK:
         * - with(): Query oluşturulurken (query builder'da)
         * - load(): Query execute edildikten sonra (instance'ta)
         * 
         * Bu method'da: model zaten alınmış, load() kullanabiliriz
         */
        $ticket->load(['order.user', 'ticketType.event']);

        /**
         * ADIM 2: VIEW'E GÖNDER
         * 
         * View'te erişim:
         * {{ $ticket->id }}  // Bilet ID
         * {{ $ticket->order->user->name }}  // Müşteri adı
         * {{ $ticket->ticketType->event->title }}  // Etkinlik adı
         */
        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * ============================================================
     * YENİ BİLET FORMU - CREATE
     * ============================================================
     * 
     * ROUTE:
     * GET /admin/tickets/create
     * 
     * AÇIKLAMA:
     * Yeni bilet oluşturma formunu göster
     * 
     * RETURN:
     * View: admin.tickets.create
     * Data: $ticketTypes, $orders, $statuses
     */
    public function create()
    {
        /**
         * Hangi bilet tipi seçilecek? (dropdown)
         */
        $ticketTypes = TicketType::with('event')->get();
        
        /**
         * Hangi sipariş'e bağlanacak? (dropdown veya yeni oluştur)
         */
        $orders = Order::with('user', 'event')->get();
        
        /**
         * Hangi status ile başlanacak? (dropdown)
         */
        $statuses = TicketStatus::cases();

        return view('admin.tickets.create', compact('ticketTypes', 'orders', 'statuses'));
    }

    /**
     * ============================================================
     * YENİ BİLET KAYDET - STORE
     * ============================================================
     * 
     * ROUTE:
     * POST /admin/tickets
     * 
     * AÇIKLAMA:
     * Form'dan gelen veriler ile yeni bilet oluştur
     * 
     * PARAMETRELER:
     * @param StoreTicketRequest $request - Validated request
     * 
     * VALIDATION (StoreTicketRequest'te):
     * - ticket_type_id: required, exists:ticket_types
     * - order_id: nullable, exists:orders
     * - status: required, in:active,checked_in,cancelled,refunded
     * 
     * VERİ AKIŞI:
     * 1. Form gönder
     * 2. Validation kontrol et
     * 3. Quota kontrol et (stok var mı?)
     * 4. Order yok ise yeni order oluştur
     * 5. Ticket oluştur
     * 6. Quota azalt
     * 7. Redirect + success message
     * 
     * RETURN:
     * Redirect: admin.tickets.index
     */
    public function store(StoreTicketRequest $request)
    {
        /**
         * ADIM 1: VALIDATED DATA AL
         * 
         * StoreTicketRequest: Zaten validation yapılmış
         * $data = ['ticket_type_id' => 1, 'order_id' => 5, 'status' => 'active']
         */
        $data = $request->validated();
        
        /**
         * ADIM 2: BİLET TÜRÜNÜ BULU
         * 
         * findOrFail: Bulamazsa 404 hatası
         */
        $ticketType = TicketType::findOrFail($data['ticket_type_id']);

        /**
         * ADIM 3: STOK KONTROL
         * 
         * remaining_quantity <= 0 ise, daha bilet satamayız
         * 
         * NEDEN?
         * - total_quantity: 100 bilet
         * - remaining_quantity: 10 bilet kaldı
         * - Admin: 10 bilet yapabili (ama hepsi satılmış olabilir)
         * 
         * Bu kontrol: Basit quota check
         * Gerçek check: Satış sisteminde yapılır
         */
        if ($ticketType->remaining_quantity <= 0) {
            return back()->withErrors([
                'ticket_type_id' => 'Kota tamamen doldurulmuş!'
            ])->withInput();
        }

        /**
         * ADIM 4: ORDER YOKSA OLUŞTUR
         * 
         * $data['order_id'] boş ise, yeni order oluştur
         * 
         * YENİ ORDER DETAYLARI:
         * - user_id: Admin (auth()->id())
         * - event_id: Bilet tipi'nin etkinliği
         * - total_amount: 0 (Admin yönetim)
         * - status: PAID (Hemen aktif)
         */
        if (empty($data['order_id'])) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'event_id' => $ticketType->event_id,
                'total_amount' => 0,
                'status' => \App\Enums\OrderStatus::PAID->value,
            ]);
            $data['order_id'] = $order->id;
        }

        /**
         * ADIM 5: BİLET OLUŞTUR
         * 
         * Ticket::create($data)
         * - ticket_type_id, order_id, status kaydedilir
         * - id, code, created_at otomatik
         */
        Ticket::create($data);
        
        /**
         * ADIM 6: QUOTA AZALT
         * 
         * decrement('remaining_quantity', 1)
         * - SQL: UPDATE ticket_types SET remaining_quantity = remaining_quantity - 1
         * 
         * NEDEN?
         * - Bir bilet oluşturuldu
         * - Bir bilet satılabilir capacity'den çıktı
         * - Kalan quota: -1
         */
        $ticketType->decrement('remaining_quantity');

        /**
         * ADIM 7: BAŞARI MESAJI VE REDIRECT
         */
        return redirect()->route('admin.tickets.index')
            ->with('success', 'Bilet oluşturuldu.');
    }

    /**
     * ============================================================
     * BİLET DÜZENLEME FORMU - EDIT
     * ============================================================
     * 
     * ROUTE:
     * GET /admin/tickets/{id}/edit
     * 
     * AÇIKLAMA:
     * Bilet düzenleme formunu göster
     * 
     * PARAMETRELER:
     * @param Ticket $ticket - Route model binding
     * 
     * RETURN:
     * View: admin.tickets.edit
     * Data: $ticket, $ticketTypes, $statuses
     */
    public function edit(Ticket $ticket)
    {
        /**
         * İlişkileri yükle
         */
        $ticket->load(['ticketType', 'order']);
        
        /**
         * Bilet tipi değiştirilebilir mi? (select dropdown)
         */
        $ticketTypes = TicketType::with('event')->get();
        
        /**
         * Status options
         */
        $statuses = TicketStatus::cases();

        return view('admin.tickets.edit', compact('ticket', 'ticketTypes', 'statuses'));
    }

    /**
     * ============================================================
     * BİLET GÜNCELLE - UPDATE
     * ============================================================
     * 
     * ROUTE:
     * PUT /admin/tickets/{id}
     * 
     * AÇIKLAMA:
     * Form'dan gelen değişiklikleri kaydet
     * 
     * PARAMETRELER:
     * @param UpdateTicketRequest $request - Validated request
     * @param Ticket $ticket - Route model binding
     * 
     * ÖZEL LOGIC:
     * - Status değişirse, checked_in_at otomatik update
     * - CHECKED_IN -> checked_in_at = now()
     * - Başka status -> checked_in_at = null
     * 
     * RETURN:
     * Redirect: admin.tickets.show
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        /**
         * ADIM 1: VALIDATED DATA AL
         */
        $data = $request->validated();

        /**
         * ADIM 2: STATUS DEĞIŞIM LOĞU
         * 
         * Eğer status CHECKED_IN'e değişirse:
         * checked_in_at = now()
         * 
         * Başka status'a dönerse:
         * checked_in_at = null (boş)
         * 
         * Bu iki sey ile API ve UI senkron kalır
         */
        if (!empty($data['status']) && $data['status'] === TicketStatus::CHECKED_IN->value) {
            /**
             * Admin: CHECKED_IN status set et
             * Otomatik: checked_in_at = now()
             */
            $data['checked_in_at'] = now();
        } elseif (!empty($data['status']) && $data['status'] !== TicketStatus::CHECKED_IN->value) {
            /**
             * Admin: Başka status set et
             * Otomatik: checked_in_at = null
             * 
             * NEDEN?
             * - Eğer CHECKED_IN'di ama ACTIVE'e dönerse
             * - checked_in_at'in silinmesi gerekir (geçersiz olur)
             */
            $data['checked_in_at'] = null;
        }

        /**
         * ADIM 3: TICKET'ı GÜNCELLE
         */
        $ticket->update($data);

        /**
         * ADIM 4: SHOW SAYFASINA REDIRECT
         */
        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Bilet güncellendi.');
    }

    /**
     * ============================================================
     * BİLET SİL - DESTROY
     * ============================================================
     * 
     * ROUTE:
     * DELETE /admin/tickets/{id}
     * 
     * AÇIKLAMA:
     * Bileti iptal et ve quota'yı iade et
     * 
     * PARAMETRELER:
     * @param Ticket $ticket - Route model binding
     * 
     * OPERASYON:
     * 1. Bileti CANCELLED status'a set et
     * 2. Quota'yı arttır (bir bilet daha satılabilir oldu)
     * 3. Redirect + success
     * 
     * NEDEN DELETE DEĞİL UPDATE?
     * - Bileti tamamen silmemek (audit trail gerekli)
     * - Status = CANCELLED ile işaretlemek
     * - İstatistikler için kayıt kalsın
     * 
     * RETURN:
     * Redirect: admin.tickets.index
     */
    public function destroy(Ticket $ticket)
    {
        // Idempotency + concurrency protection
        DB::transaction(function () use (&$ticket) {
            // Ticket'i lock ile çek ve status kontrol et (idempotency guard)
            $ticket = Ticket::lockForUpdate()->findOrFail($ticket->id);
            
            // Zaten CANCELLED veya REFUNDED ise tekrar işlem yapma
            if (in_array($ticket->status, [TicketStatus::CANCELLED, TicketStatus::REFUNDED], true)) {
                return;
            }
            
            /**
             * BİLETİ İPTAL ET (Delete değil, Status değişir)
             */
            $ticket->update(['status' => TicketStatus::CANCELLED]);

            /**
             * QUOTA İADE ET
             * 
             * increment('remaining_quantity', 1)
             * - SQL: UPDATE ... SET remaining_quantity = remaining_quantity + 1
             * 
             * NEDEN?
             * - Bilet silindi (iptal)
             * - Capacity'ye geri döndü
             * - Kalan quota: +1 (başka bilet satılabilir)
             */
            $ticketType = TicketType::lockForUpdate()->findOrFail($ticket->ticket_type_id);
            $ticketType->increment('remaining_quantity');
        });

        /**
         * BAŞARI MESAJI VE REDIRECT
         */
        return redirect()->route('admin.tickets.index')
            ->with('success', 'Bilet iptal edildi.');
    }

    /**
     * AJAX: Bileti check-in yap (ACTIVE → CHECKED_IN)
     * 
     * POST /admin/tickets/{ticket}/checkin
     * JSON response, DB::transaction + lockForUpdate ile double check-in koruması
     */
    public function checkin(Ticket $ticket)
    {
        // Order status kontrolü - sadece PAID order'lar check-in yapılabilir
        $order = $ticket->order;
        
        if (!$order || $order->status !== \App\Enums\OrderStatus::PAID) {
            $message = 'Bu bilet için ödeme tamamlanmamış.';
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            
            return back()->with('error', $message);
        }

        $result = DB::transaction(function () use ($ticket) {
            // Ticket'i lock ile çek (double check-in koruması)
            $freshTicket = Ticket::lockForUpdate()->findOrFail($ticket->id);
            
            // Sadece ACTIVE durumundaki biletler check-in yapılabilir
            if ($freshTicket->status !== TicketStatus::ACTIVE) {
                $message = 'Bu bilet check-in yapılamaz. Durumu: ' . $freshTicket->status->value;
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

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['ticket'] ?? null
        ], $result['status']);
    }

    /**
     * AJAX: Bilet check-in'i geri al (CHECKED_IN → ACTIVE)
     * 
     * POST /admin/tickets/{ticket}/checkin-undo
     * JSON response, DB::transaction + lockForUpdate
     */
    public function checkinUndo(Ticket $ticket)
    {
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

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['ticket'] ?? null
        ], $result['status']);
    }

    /**
     * AJAX: Bileti iptal et (ACTIVE → CANCELLED) + stok iade
     * 
     * POST /admin/tickets/{ticket}/cancel-ticket
     * JSON response, idempotent (status guard + lockForUpdate)
     */
    public function cancelTicket(Ticket $ticket)
    {
        // Idempotency + concurrency protection
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

        return response()->json([
            'success' => true,
            'message' => 'Bilet başarıyla iptal edildi.',
            'data' => ['ticket' => $ticket->fresh()]
        ]);
    }
}
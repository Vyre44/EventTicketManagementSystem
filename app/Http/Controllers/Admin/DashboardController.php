<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\OrderStatus;

/**
 * Admin Dashboard Controller
 * 
 * Aggregate statistics: count(), where() queries
 * Enum-based filtering: TicketStatus::CHECKED_IN, OrderStatus::PAID
 * Returns view with $stats array
 */
class DashboardController extends Controller
{
    // Constructor-based middleware application (Laravel pattern)
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Dashboard view - aggregate statistics
     * Query Builder: count(), where() with enum filtering
     */
    public function index()
    {
        /**
         * Statistics aggregation: 
         * Key => Value pairs:
         * - 'total_events': Kaç etkinlik oluşturulmuş?
         * - 'total_orders': Kaç sipariş alınmış?
         * - 'total_tickets': Kaç bilet satıldı?
         * - 'checked_in_tickets': Kaç bilet check-in edilmiş?
         * - 'paid_orders': Kaç sipariş ödenmiş?
         * 
         * HER STATIK BİR DATABASE SORGUSU:
         * - Event::count() -> SELECT COUNT(*) FROM events
         * - Order::count() -> SELECT COUNT(*) FROM orders
         * - Vb.
         */
        $stats = [
            /**
             * TOPLAM ETKİNLİK SAYISI
             * 
             * SQL: SELECT COUNT(*) FROM events
             * 
             * AÇIKLAMA:
             * Sisteme kaç etkinlik eklenmişse onun sayısını getir
             * DRAFT, PUBLISHED, CANCELLED - tümü sayılır
             * 
             * DASHBOARD'DA KULLANIM:
             * "Toplam {{ $stats['total_events'] }} etkinlik"
             */
            'total_events' => Event::count(),

            /**
             * TOPLAM SİPARİŞ SAYISI
             * 
             * SQL: SELECT COUNT(*) FROM orders
             * 
             * AÇIKLAMA:
             * Kaç sipariş alınmışsa (satın alınmışsa)
             * PENDING, PAID, CANCELLED, REFUNDED - tümü sayılır
             * 
             * DASHBOARD'DA KULLANIM:
             * "Toplam {{ $stats['total_orders'] }} sipariş"
             */
            'total_orders' => Order::count(),

            /**
             * TOPLAM BİLET SAYISI
             * 
             * SQL: SELECT COUNT(*) FROM tickets
             * 
             * AÇIKLAMA:
             * Tüm sipariş'lerin tüm biletlerinin sayısı
             * Her sipariş 1-N bilet içerebilir
             * 
             * ÖRNEK:
             * - Sipariş 1: 5 bilet
             * - Sipariş 2: 3 bilet
             * - Toplam: 8 bilet
             * 
             * DASHBOARD'DA KULLANIM:
             * "Toplam {{ $stats['total_tickets'] }} bilet satıldı"
             */
            'total_tickets' => Ticket::count(),

            /**
             * CHECK-İN YAPILAN BİLET SAYISI
             * 
             * SQL: SELECT COUNT(*) FROM tickets WHERE status = 'checked_in'
             * 
             * AÇIKLAMA:
             * Kaç bilet etkinliğe girişte kullanıldı?
             * 
             * İş Akışı:
             * 1. Bilet satıldı (ACTIVE durumuna geldi)
             * 2. Kullanıcı etkinliğe girdi
             * 3. Bilet tarandı (CHECK-IN yapıldı)
             * 4. Bilet durumu: ACTIVE -> CHECKED_IN
             * 
             * Bu sayı: Kaç ziyaretçi giriş yapmış?
             * 
             * DASHBOARD'DA KULLANIM:
             * "{{ $stats['checked_in_tickets'] }} bilet kullanıldı (%.1 oranı)"
             */
            'checked_in_tickets' => Ticket::where('status', TicketStatus::CHECKED_IN)->count(),

            /**
             * ÖDENEN SİPARİŞ SAYISI
             * 
             * SQL: SELECT COUNT(*) FROM orders WHERE status = 'paid'
             * 
             * AÇIKLAMA:
             * Kaç sipariş ödeme başarıyla tamamlandı?
             * 
             * Sipariş Workflow:
             * PENDING -> [Ödeme yapıldı] -> PAID -> [Biletler aktif hale gelir]
             * 
             * Bu sayı:
             * - Başarılı işlemlerin sayısı
             * - Potansiyel gelir
             * - Ziyaretçi dönüşüm oranı
             * 
             * DASHBOARD'DA KULLANIM:
             * "{{ $stats['paid_orders'] }} ödenen sipariş"
             */
            'paid_orders' => Order::where('status', OrderStatus::PAID)->count(),
        ];

        /**
         * ADIM 2: BLADE TEMPLATE'E GÖNDER
         * 
         * view('admin.dashboard', compact('stats'))
         * 
         * view():
         * - Laravel helper function
         * - Path: resources/views/admin/dashboard.blade.php
         * - "admin.dashboard" -> admin/dashboard.blade.php
         * 
         * compact('stats'):
         * - PHP built-in function
         * - Array oluşturur: ['stats' => $stats]
         * - Template'te $stats olarak erişilebilir
         * 
         * RETURN:
         * Rendered HTML string
         * Tarayıcıya gönderilir
         * 
         * TEMPLATE KODU ÖRNEĞI (dashboard.blade.php):
         * <div class="stat-card">
         *     <h3>Toplam Etkinlik</h3>
         *     <p class="number">{{ $stats['total_events'] }}</p>
         * </div>
         */
        return view('admin.dashboard', compact('stats'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;

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
             * TOPLAM ORGANIZATÖR SAYISI
             * 
             * SQL: SELECT COUNT(*) FROM users WHERE role = 'organizer'
             * 
             * AÇIKLAMA:
             * Sistemde kaç organizatör kullanıcı vardır?
             * 
             * DASHBOARD'DA KULLANIM:
             * "Toplam {{ $stats['total_organizers'] }} organizatör"
             */
            'total_organizers' => User::where('role', UserRole::ORGANIZER)->count(),

            /**
             * TOPLAM KATILIMCI SAYISI
             * 
             * SQL: SELECT COUNT(*) FROM users WHERE role = 'attendee'
             * 
             * AÇIKLAMA:
             * Sistemde kaç katılımcı (müşteri) kullanıcı vardır?
             * 
             * DASHBOARD'DA KULLANIM:
             * "Toplam {{ $stats['total_attendees'] }} katılımcı"
             */
            'total_attendees' => User::where('role', UserRole::ATTENDEE)->count(),

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
             * TOPLAM BİLET SAYISI (Aktif/Geçerli)
             * 
             * SQL: SELECT COUNT(*) FROM tickets WHERE status IN ('active', 'checked_in')
             * 
             * AÇIKLAMA:
             * İptal ve iade hariç, aktif ve kullanılmış bilet sayısı
             * Geçerli biletlerin sayısı = toplam satılan - iptal - iade
             * 
             * ÖRNEK:
             * - Satılan: 10 bilet
             * - İptal: 2 bilet
             * - İade: 1 bilet
             * - Geçerli/Aktif: 7 bilet
             * 
             * DASHBOARD'DA KULLANIM:
             * "Toplam {{ $stats['total_tickets'] }} geçerli bilet"
             */
            'total_tickets' => Ticket::whereIn('status', [TicketStatus::ACTIVE, TicketStatus::CHECKED_IN])->count(),

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

              /**
               * TOPLAM GELİR - PAID Siparişlerin Tutarları Toplamı
               * 
               * Açıklama:
               * - Sadece ödenmiş (PAID) siparişlerin tutarları toplanır
               * - Cancelled/Refunded siparişler dahil değildir
               * - Bütçe planlama, finansal rapor, KPI takibi için kullanılır
               * 
               * Veri Türü:
               * - decimal(10,2)
               * - Örn: 1234.50 (TL)
               * 
               * SQL Equivalenti:
               * SELECT SUM(total_amount) FROM orders WHERE status = 'paid'
               * 
               * DASHBOARD'DA KULLANIM:
               * "Toplam Gelir (PAID): {{ $stats['total_revenue'] ?? 0 }} ₺"
               */
              'total_revenue' => Order::where('status', OrderStatus::PAID)->sum('total_amount'),
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

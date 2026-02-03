<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;

/**
 * ============================================================
 * ADMIN RAPOR CONTROLLER
 * ============================================================
 * 
 * AMAÇ:
 * Admin'in etkinlik ve bilet verilerini görüntülemesi
 * Detaylı raporlar ve CSV export'u
 * 
 * ROUTES:
 * - GET /admin/reports/events/{event}/tickets (HTML)
 * - GET /admin/reports/events/{event}/tickets/export (CSV)
 * 
 * MIDDLEWARE:
 * - auth: Giriş yapılmış mı?
 * - role:admin: Admin rolü var mı?
 * 
 * FEATURES:
 * - Bilet listesi gösterme (pagination)
 * - Status filtresi (ACTIVE, CHECKED_IN, CANCELLED, REFUNDED)
 * - Search (bilet ID, kod, email)
 * - CSV export (download)
 * 
 * DATABASE OPERASYONLARI:
 * - Ticket model ile sorgular
 * - Eager loading ile ilişkileri birlikte getirme
 * - Filter ve search ile verileri daraltma
 */
class ReportController extends Controller
{
    /**
     * CONSTRUCTOR - Middleware Kontrolü
     * 
     * AÇIKLAMA:
     * Admin yetkisini kontrol et
     * Giriş yapmamış veya admin değilse 403 Forbidden
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * ============================================================
     * ETKİNLİK BİLET RAPORU - HTML GÖRÜNÜMÜ
     * ============================================================
     * 
     * ROUTE:
     * GET /admin/reports/events/{event}/tickets
     * 
     * AÇIKLAMA:
     * Belirli bir etkinliğin tüm biletlerini tabloyla göster
     * Filter ve search imkanları sunmak
     * 
     * PARAMETRELER:
     * @param Request $request - HTTP request (filters/search/pagination)
     * @param Event $event - Route model binding ile bilet sorgulanan event
     * 
     * QUERY PARAMETERS:
     * - status=active (Filter): Sadece belirli status biletleri
     * - search=text (Search): ID, kod veya email ile ara
     * - per_page=20 (Pagination): Her sayfada kaç bilet?
     * - page=2 (Pagination): Kaçıncı sayfa?
     * 
     * RETURN:
     * View: admin.reports.event_tickets
     * Data: $event, $tickets (paginated)
     * 
     * ÖRNEK URL:
     * /admin/reports/events/5/tickets?status=active&search=john&per_page=50
     */
    public function eventTickets(Request $request, Event $event)
    {
        /**
         * ADIM 1: QUERY BUILDER OLUŞTUR
         * 
         * Ticket model'inde başla
         * Event'e ait ticket'leri bul
         * Relation'ları eager load et (N+1 sorgu avoid)
         */
        $query = Ticket::whereHas('ticketType', function ($q) use ($event) {
            /**
             * whereHas: İlişkili model üzerinden filter
             * 
             * Açıklama:
             * Bilet'in ticketType'ı var mı?
             * Ve ticketType'ın event'i event_id mi eşleşiyor?
             * 
             * SQL (yaklaşık):
             * SELECT * FROM tickets
             * WHERE ticket_type_id IN (
             *     SELECT id FROM ticket_types WHERE event_id = {$event->id}
             * )
             */
            $q->where('event_id', $event->id);
        })
        ->with(['ticketType', 'order.user']);
        /**
         * with(): Eager loading - ilişkileri birlikte getir
         * 
         * ticketType: Bilet'in tipi (VIP, Standart, vb.)
         * order.user: Bilet'i satın alan kullanıcı
         * 
         * NEDEN?
         * - Query sayısını azalt (N+1 sorgu avoid)
         * - View'te $ticket->ticketType->name erişimi kolay
         * - Frontend'de relationship data mevcut
         * 
         * ÖRNEK (Without eager loading):
         * foreach ($tickets as $ticket) {
         *     echo $ticket->ticketType->name;  // Her iterasyon + 1 query
         * }
         * // Total: 1 + count($tickets) query! (KÖTÜ)
         * 
         * ÖRNEK (With eager loading):
         * foreach ($tickets as $ticket) {
         *     echo $ticket->ticketType->name;  // Zaten yüklü
         * }
         * // Total: 2 queries (İYİ)
         */

        /**
         * ADIM 2: STATUS FİLTRESİ UYGULA
         * 
         * URL: /tickets?status=checked_in
         * -> Sadece checked-in biletleri göster
         */
        if ($request->filled('status')) {
            /**
             * $request->filled('status'): 
             * - Parameter var mı?
             * - Boş değil mi?
             * 
             * Boş parametreleri ignore et:
             * ?status=  -> ignore et (filled() = false)
             * ?status=active  -> uygula (filled() = true)
             */
            $query->where('status', $request->input('status'));
        }

        /**
         * ADIM 3: SEARCH UYGULA
         * 
         * URL: /tickets?search=john
         * Arama alanları:
         * - Bilet ID: ticket id = 123
         * - Bilet Kodu: ticket code like "%john%"
         * - Kullanıcı Email: user email like "%john%"
         */
        if ($request->filled('search')) {
            $search = $request->input('search');
            
            $query->where(function ($q) use ($search) {
                /**
                 * WHERE (
                 *     id = {$search}
                 *     OR code LIKE %{$search}%
                 *     OR (select ... where user.email LIKE %{$search}%)
                 * )
                 * 
                 * OR lopikasy:
                 * İd eşleşiyorsa veya kod benzerse veya email benzerse -> göster
                 */
                $q->where('id', $search)
                  ->orWhere('code', 'like', "%$search%")
                  ->orWhereHas('order.user', function ($subq) use ($search) {
                      /**
                       * orWhereHas: İlişkili model üzerinden OR filter
                       * 
                       * order.user.email LIKE %{$search}%
                       * 
                       * ÖRNEK:
                       * search=john
                       * -> Email'de "john" varsa bulur
                       * -> john@example.com, john.smith@gmail.com, vb.
                       */
                      $subq->where('email', 'like', "%$search%");
                  });
            });
        }

        /**
         * ADIM 4: PAGINATION UYGULA
         * 
         * paginate(per_page):
         * - Verileri sayfalara böl
         * - Links ve metadata ekle
         * - Blade template'de {{ $tickets->links() }}
         * 
         * Default: 20 bilet/sayfa
         * User isteği: per_page parameter ile override et
         * 
         * NEDEN PAGINATION?
         * - 10.000 bilet hepsi aynı anda load edilmez
         * - Page load time yüksek olur
         * - Memory yüksek olur
         * - Pagination: 20 bilet/sayfa, hızlı ve verimli
         */
        $tickets = $query->latest()->paginate($request->input('per_page', 20));
        /**
         * latest(): En yeni biletleri başa al
         * (created_at DESC)
         * 
         * paginate(20): 20 item/page
         */

        /**
         * ADIM 5: VIEW'E GÖNDER
         * 
         * return view('admin.reports.event_tickets', compact('event', 'tickets'))
         * 
         * compact():
         * ['event' => $event, 'tickets' => $tickets] oluşturur
         * 
         * View'te erişim:
         * {{ $event->title }}  // Etkinlik adı
         * @foreach($tickets as $ticket)
         *     {{ $ticket->id }}  // Bilet ID
         * @endforeach
         */
        return view('admin.reports.event_tickets', compact('event', 'tickets'));
    }

    /**
     * ============================================================
     * ETKİNLİK BİLET RAPORU - CSV EXPORT
     * ============================================================
     * 
     * ROUTE:
     * GET /admin/reports/events/{event}/tickets/export
     * 
     * AÇIKLAMA:
     * Biletleri CSV formatında indir
     * Excel, Google Sheets, vb programlarda aç
     * 
     * PARAMETRELER:
     * @param Request $request - HTTP request (filters/search, HTML'deki ile aynı)
     * @param Event $event - Route model binding
     * 
     * QUERY PARAMETERS:
     * - status=active (Filter)
     * - search=text (Search)
     * 
     * RETURN:
     * CSV dosyası (download)
     * 
     * ÖRNEK URL:
     * /admin/reports/events/5/tickets/export?status=checked_in
     * 
     * DOSYA ADI:
     * tickets_5_2026-02-15_143000.csv
     * (event_id_YYYY-MM-DD_HHMMSS.csv)
     * 
     * CSV İÇERİĞİ:
     * ticket_id, event_title, ticket_type, ticket_status, checked_in_at, user_email, order_status
     * 1, Konser 2026, VIP, checked_in, 2026-02-15 14:30:00, john@example.com, paid
     * 2, Konser 2026, Standard, active, , jane@example.com, paid
     * 
     * KULLANIM:
     * 1. Admin: "Export" butonu
     * 2. CSV dosyası indirilir
     * 3. Excel'de aç veya parse et
     */
    public function exportEventTickets(Request $request, Event $event)
    {
        /**
         * ADIM 1: QUERY OLUŞTUR (eventTickets() ile aynı filtreleme)
         * 
         * Unterschied:
         * - HTML: paginate() ile sayfalama
         * - CSV: get() ile tümü al (dosyada olsun diye)
         */
        $query = Ticket::whereHas('ticketType', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })->with(['ticketType', 'order.user']);

        /**
         * ADIM 2: FILTER'LARı UYGULA (HTML ile aynı)
         */
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

        /**
         * ADIM 3: TÜMO SONUÇLARI AL
         * 
         * get(): Pagination olmadan tüm sonuçlar
         * 
         * DİKKAT: 100.000 bilet varsa hepsi RAM'e yüklenir
         * Büyük exportlar için chunk() kullanılabilir
         */
        $tickets = $query->get();

        /**
         * ADIM 4: CSV RESPONSE OLUŞTUR VE GÖNDER
         */
        return $this->buildCsvResponse($event, $tickets);
    }

    /**
     * ============================================================
     * CSV RESPONSE OLUŞTUR (Native PHP)
     * ============================================================
     * 
     * AÇIKLAMA:
     * CSV dosyası oluştur ve HTTP response olarak gönder
     * 
     * PARAMETRELER:
     * @param Event $event - Etkinlik bilgisi (dosya adında)
     * @param Collection $tickets - Biletler
     * 
     * RETURN:
     * HTTP Response: CSV dosyası (indir)
     * 
     * NEDEN NATIVE PHP?
     * - Library dependency yok
     * - Hızlı ve verimli
     * - Memory efficient (fopen/fputcsv)
     * 
     * ALTERNATIF: League CSV veya Laravel Excel
     * Ancak bu proje için native PHP yeterli
     */
    private function buildCsvResponse(Event $event, $tickets)
    {
        /**
         * ADIM 1: DOSYA ADI OLUŞTUR
         * 
         * Format: tickets_{event_id}_{YYYY-MM-DD_HHMMSS}.csv
         * Örnek: tickets_5_2026-02-15_143000.csv
         * 
         * NEDEN TIMESTAMP?
         * - Aynı etkinlik için birden fazla export
         * - Her export unique isim
         * - Admin'in hangisi ne zaman alındığını bilmesi
         */
        $filename = "tickets_{$event->id}_" . now()->format('Y-m-d_His') . ".csv";

        /**
         * ADIM 2: HTTP HEADERS AYARLA
         * 
         * Content-Type: text/csv; charset=utf-8
         * -> Bu dosya CSV'dir demek
         * 
         * Content-Disposition: attachment; filename="..."
         * -> Dosyayı download et ve adını şu yap
         * 
         * Cache-Control: must-revalidate, ...
         * -> Browser'ın CSV'yi cache'lemesini engelle
         * -> (Çünkü her export farklı olabilir)
         */
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        /**
         * ADIM 3: IN-MEMORY CSV DOSYASI OLUŞTUR
         * 
         * fopen('php://memory', 'r+'):
         * - Disk'e yazılmayan, RAM'deki dosya
         * - Küçük-orta CSV'ler için yeterli
         * - Büyük dosyalar için php://temp kullanlır
         * 
         * 'r+' modu:
         * - Read + Write
         * - Yazacağız ve sonra okuyacağız
         */
        $content = fopen('php://memory', 'r+');

        /**
         * ADIM 4: CSV HEADER SATIRI YAZMA
         * 
         * fputcsv($file, $array):
         * - Array'i CSV satırına dönüştür
         * - Virgüllere ayır
         * - Özel karakterleri escape et
         * 
         * Çıktı (yaklaşık):
         * ticket_id,event_title,ticket_type,ticket_status,checked_in_at,user_email,order_status
         */
        fputcsv($content, [
            'ticket_id',
            'event_title',
            'ticket_type',
            'ticket_status',
            'checked_in_at',
            'user_email',
            'order_status'
        ]);

        /**
         * ADIM 5: CSV VERI SATIRLARI YAZMA
         * 
         * foreach: Her bilet için satır yazma
         * 
         * fputcsv(array):
         * 123,Konser 2026,VIP,checked_in,2026-02-15 14:30:00,john@example.com,paid
         */
        foreach ($tickets as $ticket) {
            fputcsv($content, [
                /**
                 * Bilet ID
                 */
                $ticket->id,
                
                /**
                 * Etkinlik Adı
                 */
                $event->title,
                
                /**
                 * Bilet Tipi
                 * Relation: ticket.ticketType.name
                 */
                $ticket->ticketType->name,
                
                /**
                 * Bilet Durumu (Enum value)
                 * Enum value: TicketStatus::CHECKED_IN->value => 'checked_in'
                 */
                $ticket->status->value,
                
                /**
                 * Check-in Zamanı
                 * Nullable: Bilet check-in edilmemişse boş
                 * 
                 * $ticket->checked_in_at?->format()
                 * - ?->: Null-safe operator
                 * - checked_in_at null ise '' döner
                 * - Değilse format('Y-m-d H:i:s') uygulanır
                 * 
                 * Çıktı:
                 * "2026-02-15 14:30:00" (check-in edildiyse)
                 * "" (check-in edilmediyse)
                 */
                $ticket->checked_in_at?->format('Y-m-d H:i:s') ?? '',
                
                /**
                 * Kullanıcı Email
                 * Relation: ticket.order.user.email
                 * 
                 * Nullable chain:
                 * - Bilet'in order'ı var mı?
                 * - Order'ın user'ı var mı?
                 * - User'ın email'i var mı?
                 * - Yoksa: 'N/A'
                 * 
                 * Çıktı:
                 * "john@example.com" (müşteri varsa)
                 * "N/A" (bilet silinmişse vb.)
                 */
                $ticket->order?->user?->email ?? 'N/A',
                
                /**
                 * Sipariş Durumu
                 * Relation: ticket.order.status
                 * 
                 * Enum value: OrderStatus::PAID->value => 'paid'
                 * Nullable: Order yoksa 'N/A'
                 * 
                 * Çıktı:
                 * "paid" (ödenmişse)
                 * "N/A" (order yoksa)
                 */
                $ticket->order?->status?->value ?? 'N/A',
            ]);
        }

        /**
         * ADIM 6: DOSYA İÇERİĞİNİ OKU
         * 
         * rewind($content): Dosya işaretçisini başa al
         * stream_get_contents(): Tüm içeriği string'e dönüştür
         * fclose(): Dosya kaynağını kapat
         */
        rewind($content);
        $csv = stream_get_contents($content);
        fclose($content);

        /**
         * ADIM 7: CSV İçERİĞİNİ RESPONSE OLARAK GÖNDER
         * 
         * response($content, $status, $headers):
         * - $content: CSV string
         * - $status: 200 (OK)
         * - $headers: Content-Type, Content-Disposition, Cache, vb.
         * 
         * Sonuç:
         * - Tarayıcı headers okur
         * - Content-Disposition: attachment demek -> download başlar
         * - Dosya adı: tickets_5_2026-02-15_143000.csv
         * - User'ın Downloads klasörüne indirilir
         * - Excel/Google Sheets'de aç -> Tablo gösterilir
         */
        return response($csv, 200, $headers);
    }
}

<?php

namespace App\Enums;

/**
 * ============================================================
 * SİPARİŞ DURUMU ENUM
 * ============================================================
 * 
 * AMAÇ:
 * Satın alma siparişinin ödeme ve tamamlanma durumunu temsil etmek
 * 
 * SİPARİŞ AKIŞI:
 * 
 * [Kullanıcı "Satın Al" tıklar]
 *        ↓
 * Order oluşturulur (PENDING)
 *        ↓
 * [Ödeme Sayfasına Gönder]
 *        ↓
 * [Kredi Kartı Bilgilerini Gir]
 *        ↓
 * [Ödeme işlemcisi onay verir]
 *        ↓
 * Order status: PENDING -> PAID
 * Biletler oluşturulur (ACTIVE)
 *        ↓
 * [E-ticket'ler gönderilir]
 *        ↓
 * [Kullanıcı etkinliğe katılır]
 * 
 * ENUM NEDİR?
 * Type-safe constants
 * String'den daha güvenli:
 * - 'pending' yerine OrderStatus::PENDING yazıp hata yakalanır
 * - Database'de 'pending' depolanır
 * - PHP'de OrderStatus enum kalır
 * 
 * CAST:
 * Order Model'de: 'status' => OrderStatus::class
 * $order->status = OrderStatus::PENDING  // Type safe
 * $order->status->value  // 'pending' (string)
 * 
 * DATABASE:
 * orders table'ında: status ENUM('pending', 'paid', 'cancelled', 'refunded')
 * Laravel'in cast sistem otomatik dönüştürür
 * 
 * RELATIONSHIPS:
 * - Order hasMany Ticket
 * - Ticket'ler Order'ın status'undan bağımsız olabilir
 * - Ama genelde Order PAID -> Ticket'ler ACTIVE
 */
enum OrderStatus: string
{
    /**
     * PENDING - BEKLEMEDEAİ
     * 
     * AÇIKLAMA:
     * Sipariş oluşturulmuş ama ödeme henüz yapılmadı
     * Kullanıcı ödeme sayfasında veya ödemeden vaz geçti
     * 
     * NE OLUR?
     * - Order: Veritabanında kaydedilir
     * - Ticket'ler: BİLEMIŞTİR (opsiyonal)
     * - Email: Ödemeyi tamamlayın diye hatırlatma
     * - Sepet: Öğeler beklemede kalır
     * 
     * OPERASYON:
     * 1. POST /checkout (veya benzer)
     * 2. Order::create(['status' => 'pending', ...])
     * 3. Ödeme sayfasına yönlendir
     * 4. Kullanıcı ödeme yapar veya iptal eder
     * 
     * DURATION:
     * - Birkaç dakika (ödeme sırasında)
     * - Saatler (sesli hatırlatma)
     * - Günler (email hatırlatması)
     * 
     * TIMEOUT:
     * Genelde 15-30 dakika sonra otomatik CANCELLED
     * Stripe/PayPal tarafından güncellenir
     * 
     * DATABASE:
     * orders.status = 'pending'
     * orders.created_at = 2026-02-15 10:00:00
     * 
     * QUERY:
     * $pending = Order::where('status', OrderStatus::PENDING)->get();
     * $old = Order::where('status', OrderStatus::PENDING)
     *     ->where('created_at', '<', now()->subHours(1))
     *     ->get();  // 1 saatten eski siparişler
     * 
     * RAPORLAMA:
     * - Conversion funnel start
     * - Kaç sipariş başlandı?
     * - Tamamlama oranı? (PENDING -> PAID)
     */
    case PENDING = 'pending';

    /**
     * PAID - ÖDENEN
     * 
     * AÇIKLAMA:
     * Ödeme başarıyla tamamlandı
     * Biletler aktif, etkinliğe katılım yapılabilir
     * 
     * NE OLUR?
     * - Order status: PENDING -> PAID
     * - Ticket'ler: Oluşturulur (ACTIVE)
     * - Email: E-ticket'lerle gönderilir
     * - Dashboard: Kullanıcı biletlerini görebilir
     * - QR Code: Bilet'te QR kod oluşturulur
     * - Check-in: Etkinliğe girerken taranabilir
     * 
     * OPERASYON:
     * 1. Ödeme işlemcisi (Stripe/PayPal) onay verir
     * 2. Webhook: POST /webhooks/payment-confirmed
     * 3. Order::find($id)->markAsPaid()
     * 4. Status: PENDING -> PAID
     * 5. Ticket::create() - her bilet için
     * 6. Mail::send(TicketEmail)
     * 
     * TIMING:
     * - Ödeme yapıldığında hemen
     * - Biletler saniye içinde gönderilir
     * - Kullanıcı hemen biletlerini görebilir
     * 
     * DATABASE:
     * orders.status = 'paid'
     * orders.paid_at = 2026-02-15 10:05:00
     * 
     * RELATIONSHIPS:
     * Order.tickets: hasMany Ticket
     * -> All tickets: status = 'active'
     * -> count = Order.quantity
     * 
     * QUERY:
     * $paid = Order::where('status', OrderStatus::PAID)->get();
     * $revenue = Order::where('status', OrderStatus::PAID)
     *     ->sum('total_amount');  // Toplam gelir
     * 
     * RAPORLAMA:
     * - Toplam satış sayısı
     * - Toplam gelir
     * - Conversion rate
     * - Revenue per event
     * - Customer acquisition cost
     * 
     * REFUND POSSİBİLİTY:
     * PAID durumunda refund yapılabilir:
     * Order.refund() -> status: PAID -> REFUNDED
     * Ticket'ler: ACTIVE -> REFUNDED
     */
    case PAID = 'paid';

    /**
     * CANCELLED - İPTAL EDİLMİŞ
     * 
     * AÇIKLAMA:
     * Sipariş kullanıcı tarafından iptal edildi
     * Ödeme yapılmadı (veya geri alındı)
     * Biletler geçersiz
     * 
     * NEDENLERİ:
     * 1. Kullanıcı iptal etti (ödeme sayfasında "İptal" tıkladı)
     * 2. Ödeme işlemcisi red etti (kart declined)
     * 3. Timeout: 30 dakika ödeme yapılmadı
     * 4. Sistem hatası
     * 5. Admin iptal etti
     * 
     * TIMELINE:
     * PENDING -> [Kullanıcı iptal] -> CANCELLED
     * PENDING -> [Timeout] -> CANCELLED
     * PENDING -> [Kart red] -> CANCELLED
     * 
     * NE OLUR?
     * - Biletler: Oluşturulmamışsa hiç, oluşturulmuşsa invalid
     * - Email: Sipariş iptal edildi mesajı
     * - Sepet: Reset
     * - Money: Ödeme yapılmadıysa nakit yok
     * 
     * OPERASYON:
     * 1. Kullanıcı/Admin iptal et
     * 2. Order::find($id)->cancel()
     * 3. Status: PENDING -> CANCELLED
     * 4. Ticket'ler: delete (eğer varsa)
     * 5. Email notification
     * 
     * DATABASE:
     * orders.status = 'cancelled'
     * orders.cancelled_at = 2026-02-15 10:10:00
     * 
     * QUERY:
     * $cancelled = Order::where('status', OrderStatus::CANCELLED)->get();
     * $cancelRate = Order::where('status', OrderStatus::CANCELLED)->count()
     *     / Order::count() * 100;  // İptal yüzdesi
     * 
     * RAPORLAMA:
     * - İptal sayısı
     * - İptal nedenleri
     * - İptal oranı
     * - Lost revenue
     * 
     * REFUND:
     * CANCELLED'da para iadesi genelde gerekli değil:
     * - PENDING'de cancel -> Zaten ödeme yapılmadı
     * - Ancak başarısız ödeme çekiş varsa geri alınır
     */
    case CANCELLED = 'cancelled';

    /**
     * REFUNDED - İADE EDİLMİŞ
     * 
     * AÇIKLAMA:
     * Sipariş iptal edildi VE para iade edildi
     * Kullanıcı paranın geri dönmesini beklemeye başladı
     * Biletler geçersiz
     * 
     * FARK: CANCELLED vs REFUNDED
     * - CANCELLED: Para aldıktan sonra çeşitli nedenler
     * - REFUNDED: Parayı geri verdik, işlem tamamlandı
     * 
     * NEDENLERİ:
     * 1. Kullanıcı refund istedi (etkinliğe gidemeyecek)
     * 2. Organizatör refund verdi (etkinlik iptal)
     * 3. Bilet problemi (çalışmayan QR, kayıp vb)
     * 4. Customer service (memnuniyet için)
     * 
     * OPERASYON:
     * 1. Order PAID -> Kullanıcı refund ister
     * 2. Admin: "Refund Et" tıkla
     * 3. Order::find($id)->refund()
     * 4. Ödeme işlemcisine refund komutu
     * 5. Status: PAID -> REFUNDED
     * 6. Ticket'ler: ACTIVE -> REFUNDED
     * 7. Email: Refund onayı ve ETA
     * 
     * TIMELINE:
     * PENDING -> PAID -> [Refund iste] -> REFUNDED
     * 
     * REFUND PROCESSING:
     * - API call to Stripe/PayPal
     * - Bank processing (3-5 iş günü)
     * - Money appears in account
     * 
     * DATABASE:
     * orders.status = 'refunded'
     * orders.refunded_at = 2026-02-15 11:00:00
     * 
     * RELATIONSHIPS:
     * Order.tickets: hasMany Ticket
     * -> All tickets: status = 'refunded'
     * 
     * QUERY:
     * $refunded = Order::where('status', OrderStatus::REFUNDED)->get();
     * $totalRefunded = Order::where('status', OrderStatus::REFUNDED)
     *     ->sum('total_amount');  // Toplam iade
     * 
     * RAPORLAMA:
     * - Toplam refund sayısı
     * - Toplam refund miktarı
     * - Refund oranı
     * - Refund nedenleri (tracking gerekli)
     * - Customer satisfaction
     * 
     * CHARGEBACK RISK:
     * Refund yapılmamış olmalı, yoksa customer chargeback açabilir:
     * - Kredi kartı şirketi müdahale eder
     * - Stripe/PayPal hesabına zarar
     * - Disputable transaction
     * 
     * FINAL STATE:
     * REFUNDED: Tamal state
     * Daha sonra başka duruma geçmez
     */
    case REFUNDED = 'refunded';

    /**
     * ============================================================
     * SİPARİŞ DURUMU AKIŞ TABLOSU
     * ============================================================
     * 
     * NORMAL PATH (Hep böyle olması beklenir):
     * PENDING ──[Ödeme yapılır]──> PAID ──[Biletler gönderilir]──> (Statik)
     * 
     * EARLY CANCELLATION:
     * PENDING ──[İptal]──> CANCELLED (Para yok)
     * 
     * PAYMENT FAILURE:
     * PENDING ──[Kart red]──> CANCELLED (Para yok)
     * 
     * CUSTOMER REFUND:
     * PAID ──[Refund iste]──> REFUNDED (Para geri verilir)
     * 
     * EVENT CANCELLATION:
     * PAID ──[Event cancelled]──> REFUNDED (Para geri verilir)
     * 
     * FULL FLOW WITH BOTH STATUSES:
     * Order (PENDING) → Ticket (-)
     *    ↓ [Ödeme]
     * Order (PAID) → Ticket (ACTIVE)
     *    ↓ [Check-in]
     * Ticket (CHECKED_IN) / Order (PAID)
     *    ↓ [Refund iste - Opsiyonal]
     * Order (REFUNDED) → Ticket (REFUNDED)
     * 
     * ============================================================
     * ROLE-BASED ACCESS
     * ============================================================
     * 
     * ADMIN:
     * - Tüm order'ları görebilir
     * - PAID -> REFUNDED yapabilir
     * - Özel notlar ekleyebilir
     * 
     * ORGANIZER:
     * - Kendi etkinliğinin order'larını görebilir
     * - Refund yapamaması daha güvenli
     * - Raporlar görebilir
     * 
     * ATTENDEE:
     * - Kendi order'larını görebilir
     * - PAID'den refund talebinde bulunabilir
     * - Order history görebilir
     */
}

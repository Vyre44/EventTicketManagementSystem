<?php

namespace App\Enums;

/**
 * TicketStatus Enum - State Machine
 * 
 * Workflow: ACTIVE -> CHECKED_IN -> CANCELLED/REFUNDED
 * Laravel casting: 'status' => TicketStatus::class
 * Status transitions: checkIn(), cancel(), refund() methods
 */
enum TicketStatus: string
{
    /**
     * ACTIVE - AKTIF/KULLANILABILIR
     * 
     * AÇIKLAMA:
     * Bilet satın alınmış, ödeme tamamlanmış
     * Etkinliğe girişte kullanılmaya hazır
     * 
     * NE YAPILABILIR?
     * - Check-in yapılabilir (ACTIVE -> CHECKED_IN)
     * - İptal edilebilir (ACTIVE -> CANCELLED)
     * - İade edilebilir (ACTIVE -> REFUNDED)
     * 
     * NE YAPILMAZ?
     * - İkinci kez check-in (zaten kontrol edilir)
     * - Sadece bir kez kullanılabilir
     * 
     * WORKFLOW:
     * 1. Kullanıcı bilet satın alır
     * 2. Ödeme başarılı
     * 3. Order status: PAID
     * 4. Bilet oluşturulur: ACTIVE
     * 5. E-ticket gönderilir
     * 6. Kullanıcı etkinliğe gelir
     * 
     * DATABASE:
     * tickets.status = 'active'
     * 
     * QUERY:
     * $active = Ticket::where('status', TicketStatus::ACTIVE)->get();
     * 
     * DURATION:
     * Etkinlik başlangıcından sonlandırılana kadar
     * Etkinlikten sonra → Otomatik CHECKED_IN veya CANCELLED
     */
    case ACTIVE = 'active';

    /**
     * CHECKED_IN - CHECK-İN YAPILAN
     * 
     * AÇIKLAMA:
     * Bilet etkinlik girişinde taranmış (checked-in)
     * Kullanıcı etkinliğe girişi yapılmış
     * 
     * NE OLUR?
     * - Kapıya giriş kaydedilir
     * - Saat kaydedilir (checked_in_at)
     * - Bilet geçersiz hale gelir
     * - Aynı bilet 2 kez kullanılamaz
     * 
     * OPERASYON:
     * 1. Check-in cihazında bilet okutulur (QR code)
     * 2. API: POST /organizer/tickets/{id}/checkin
     * 3. Ticket::find($id)->checkIn()
     * 4. Status: ACTIVE -> CHECKED_IN
     * 5. checked_in_at: Carbon now()
     * 6. Response: Success message
     * 
     * GERI ALMA (UNDO):
     * Admin: CHECKED_IN -> ACTIVE geri alabilir
     * checkIn operation yanılmışsa
     * 
     * DATABASE:
     * tickets.status = 'checked_in'
     * tickets.checked_in_at = 2026-02-15 14:30:00 (datetime)
     * 
     * QUERY:
     * $checkedIn = Ticket::where('status', TicketStatus::CHECKED_IN)->get();
     * $lateCheckins = Ticket::where('status', TicketStatus::CHECKED_IN)
     *     ->where('checked_in_at', '>', '2026-02-15 14:00:00')
     *     ->get();
     * 
     * RAPORLAMA:
     * - Kaç kişi giriş yaptı? (CHECKED_IN count)
     * - Giriş yoğunluğu nedir? (checked_in_at analysis)
     */
    case CHECKED_IN = 'checked_in';

    /**
     * CANCELLED - İPTAL EDİLMİŞ
     * 
     * AÇIKLAMA:
     * Bilet iptal edildi, artık geçerli değil
     * Kullanıcı etkinliğe giremez
     * 
     * NEDENLERİ:
     * 1. Admin/Organizer iptal etti
     * 2. Hatalı check-in geri alındı
     * 3. Bilet duplicate fark edildi
     * 4. Fraud tespit edildi
     * 
     * OPERASYON:
     * 1. Button: "İptal Et"
     * 2. Confirmation: "Emin misin?"
     * 3. API: POST /admin/tickets/{id}/cancel-ticket
     * 4. Ticket::find($id)->cancel()
     * 5. Status: ACTIVE -> CANCELLED
     * 6. cancelled_at: Carbon now()
     * 
     * RESULT:
     * - Bilet kapıya giriş izni kaybeder
     * - Check-in cihazı red eder
     * - Yeni bilet gerekir (opsiyonal)
     * 
     * REFUND:
     * - Bilet iptal edildiyse para iade edilebilir
     * - Order refund işlemi gerekir
     * 
     * DATABASE:
     * tickets.status = 'cancelled'
     * tickets.cancelled_at = 2026-02-15 10:00:00 (opsiyonal)
     * 
     * QUERY:
     * $cancelled = Ticket::where('status', TicketStatus::CANCELLED)->get();
     * 
     * RAPORLAMA:
     * - Kaç bilet iptal edildi?
     * - İptal oranı nedir?
     */
    case CANCELLED = 'cancelled';

    /**
     * REFUNDED - İADE EDİLMİŞ
     * 
     * AÇIKLAMA:
     * Bilet ve para iade edildi
     * Kullanıcı etkinliğe gidemez
     * Ödemesi geri verildi
     * 
     * NEDENLERİ:
     * 1. Kullanıcı refund istedi
     * 2. Organizatör refund verdi
     * 3. Event cancelled oldu
     * 4. Sistem hatası
     * 
     * WORKFLOW:
     * 1. Order refund başlanır
     * 2. Ödeme işlemci ile kontak
     * 3. Para iade onayı
     * 4. Ticket status: REFUNDED
     * 5. Order status: REFUNDED
     * 6. Müşteriye email gönderilir
     * 
     * FARK: CANCELLED vs REFUNDED
     * - CANCELLED: Bilet iptal, para iade edilmemiş
     * - REFUNDED: Bilet iptal VE para iade edilmiş
     * 
     * OPERASYON:
     * 1. Button: "Para İade Et"
     * 2. API: POST /orders/{id}/refund
     * 3. Order refund işlemi
     * 4. Tüm ticket'ler: REFUNDED
     * 5. Ödeme processed
     * 
     * DATABASE:
     * tickets.status = 'refunded'
     * tickets.refunded_at = 2026-02-15 11:00:00 (opsiyonal)
     * orders.status = 'refunded'
     * 
     * QUERY:
     * $refunded = Ticket::where('status', TicketStatus::REFUNDED)->get();
     * $totalRefunded = Ticket::where('status', TicketStatus::REFUNDED)
     *     ->whereHas('order', fn($q) => $q->where('status', OrderStatus::REFUNDED))
     *     ->count();
     * 
     * RAPORLAMA:
     * - Toplam iade miktarı?
     * - İade oranı nedir?
     * - Müşteri memnuniyeti?
     */
    case REFUNDED = 'refunded';

    /**
     * ============================================================
     * BİLET DURUMU AKIŞ TABLOSU
     * ============================================================
     * 
     * BAŞLANGIC: Sipariş oluşturulur (Order: PENDING)
     *            ↓
     *        Ödeme yapılır
     *            ↓
     *   Order: PENDING -> PAID
     *   Ticket: oluşturulur [ACTIVE]
     * 
     * NORMAL PATH (Hep böyle):
     * ACTIVE ──[Check-in]──> CHECKED_IN (FİNAL)
     * 
     * ERROR PATH 1:
     * ACTIVE ──[İptal]──> CANCELLED
     *           ↓
     *        [Para İade]
     *           ↓
     *        REFUNDED
     * 
     * ERROR PATH 2:
     * CHECKED_IN ──[Hata, Geri Al]──> ACTIVE
     *                                   ↓
     *                                [İptal]
     *                                   ↓
     *                                CANCELLED
     * 
     * NOT: REFUNDED -> geçtiğinde para iadesi de yapılmış demek
     * 
     * ============================================================
     * STATE TRANSITIONS (Allows/Denies)
     * ============================================================
     * 
     * ACTIVE:
     * ✅ Can go to: CHECKED_IN, CANCELLED, REFUNDED
     * ❌ Cannot: Tekrar ACTIVE, doğrudan REFUNDED'a
     * 
     * CHECKED_IN:
     * ✅ Can go to: CANCELLED (ACTIVE'e dönüp), REFUNDED
     * ❌ Cannot: Tekrar CHECKED_IN (2x check-in engelle)
     * 
     * CANCELLED:
     * ✅ Can go to: REFUNDED (para iade)
     * ❌ Cannot: ACTIVE'e, CHECKED_IN'e (sonlandırıldı)
     * 
     * REFUNDED:
     * ✅ Final state (değişmez)
     * ❌ Cannot: Başka duruma geçme
     */
}

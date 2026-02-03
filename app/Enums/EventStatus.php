<?php

namespace App\Enums;

/**
 * ============================================================
 * ETKİNLİK DURUMU ENUM
 * ============================================================
 * 
 * AMAÇ:
 * Etkinliğin yayın ve erişilebilirlik durumunu temsil etmek
 * 
 * ETKİNLİK YAŞAM DÖNGÜSÜ:
 * 
 * [Organizatör etkinlik oluşturur]
 *        ↓
 * DRAFT (Taslak - gizli, bilet satışı yok)
 *        ↓
 * [Tüm bilgileri doldur, bilet tiplerini ekle]
 *        ↓
 * [Yayın Yap butonu]
 *        ↓
 * PUBLISHED (Yayında - herkese görünür, bilet satışı açık)
 *        ↓
 * [Etkinlik başlangıç saati]
 *        ↓
 * STARTED (Check-in başlıyor)
 *        ↓
 * [Etkinlik bitişi]
 *        ↓
 * ENDED (Arşiv - istatistikler, raporlar)
 * 
 * ENUM NEDİR?
 * Type-safe constants
 * String'den daha güvenli:
 * - 'draft' yerine EventStatus::DRAFT yazıp hata yakalanır
 * - Database'de 'draft' depolanır
 * - PHP'de EventStatus enum kalır
 * 
 * CAST:
 * Event Model'de: 'status' => EventStatus::class
 * $event->status = EventStatus::DRAFT  // Type safe
 * $event->status->value  // 'draft' (string)
 * 
 * DATABASE:
 * events table'ında: status ENUM('published', 'draft', 'ended')
 * Laravel'in cast sistem otomatik dönüştürür
 */
enum EventStatus: string
{
    /**
     * DRAFT - TASLAK
     * 
     * AÇIKLAMA:
     * Etkinlik oluşturulmuş ama henüz yayınlanmamış
     * Gizli mod: Sadece organizatör ve admin görebilir
     * Bilet satışı kapalı
     * 
     * NE OLUR?
     * - Etkinlik veritabanında kaydedilir
     * - Searchte görünmez
     * - Bilet satışı: KAPALI
     * - Attendee'ler: Göremez
     * - Organizatör: Düzenleyebilir
     * 
     * OPERASYON:
     * 1. EventController::store() -> DRAFT with kaydedilir
     * 2. Organizatör: Detay sayfasında düzenleme yapabliir
     * 3. Bilet tipleri: Eklenebilir (DRAFT'ta)
     * 4. "Yayın Yap" butonuna tık
     * 5. Validation: Tüm alanlar doldurulmuş mı?
     * 6. Status: DRAFT -> PUBLISHED
     * 
     * GÖRÜNÜRLÜK:
     * - Event::visibleTo(Admin) -> DRAFT'lar da görünür
     * - Event::visibleTo(Organizer) -> Kendi DRAFT'ları
     * - Event::visibleTo(Attendee) -> DRAFT görünmez
     * 
     * DATABASE:
     * events.status = 'draft'
     * events.created_at = 2026-02-15 10:00:00
     * 
     * QUERY:
     * $drafts = Event::where('status', EventStatus::DRAFT)
     *     ->where('organizer_id', auth()->id())
     *     ->get();  // Organizatörün draft etkinlikleri
     * 
     * RAPORLAMA:
     * - Kaç draft etkinlik var?
     * - Kaç etkinlik yayınlandı? (Conversion rate)
     * - Ortalama draft süresi?
     * 
     * Optimization:
     * Draft'lar arama indexlerine eklenmez:
     * SELECT * FROM events WHERE status != 'draft'
     */
    case DRAFT = 'draft';

    /**
     * PUBLISHED - YAYINDA
     * 
     * AÇIKLAMA:
     * Etkinlik aktif, herkese görünür, bilet satışı açık
     * Attendee'ler bilet satın alabilir
     * Normal operasyon durumu
     * 
     * NE OLUR?
     * - Etkinlik: Search'te, catalogue'da görünür
     * - Bilet satışı: AÇIK
     * - Attendee'ler: Satın alabilir
     * - Check-in: Etkinlik saati gelince aktif
     * - Raporlar: Gerçek zamanlı update
     * 
     * OPERASYON:
     * 1. Organizatör: "Yayın Yap" tıkla
     * 2. Event::find($id)->update(['status' => EventStatus::PUBLISHED])
     * 3. Status: DRAFT -> PUBLISHED
     * 4. Event published at: now()
     * 5. Email: "Etkinlik yayınlandı" (organizatöre)
     * 6. Webhook: "event.published" event'i fire
     * 
     * DURATION:
     * Yayın zamanı -> Etkinlik sona kadar
     * Genelde: hafta, ay gibi uzun süreler
     * 
     * GÖRÜNÜRLÜK:
     * - Event::visibleTo(Admin) -> Tümü görür
     * - Event::visibleTo(Organizer) -> Kendi PUBLISHED'ları
     * - Event::visibleTo(Attendee) -> Tüm PUBLISHED'lar
     * 
     * SCOPE:
     * public function scopePublished(Builder $query)
     * {
     *     return $query->where('status', EventStatus::PUBLISHED);
     * }
     * 
     * DATABASE:
     * events.status = 'published'
     * events.published_at = 2026-02-15 11:00:00
     * 
     * QUERY:
     * $published = Event::published()->get();  // scope kullanarak
     * $soon = Event::published()
     *     ->where('start_time', '<=', now()->addDays(7))
     *     ->get();  // Yaklaşan etkinlikler
     * 
     * RAPORLAMA:
     * - Kaç etkinlik yayında?
     * - Ortalama satış sayısı
     * - Popüler etkinlikler
     * - Revenue by event
     * 
     * RESTRICTIONS:
     * - PUBLISHED etkinlik iptal edilemez (best practice)
     * - Bilet fiyatı değiştirilemez
     * - Bilet tipi silinemez
     * - Capacity azaltılamaz
     */
    case PUBLISHED = 'published';

    /**
     * ENDED - BİTMİŞ/ARŞİV
     * 
     * AÇIKLAMA:
     * Etkinlik tamamlandı, bilet satışı kapalı
     * İstatistik ve raporlama için arşiv durumu
     * 
     * NE OLUR?
     * - Bilet satışı: KAPALI
     * - Yeni bilet: Satın alınamaz
     * - Check-in: KAPALI (zaten bitiş saati geçti)
     * - Raporlar: Final (değişmez)
     * - Attendee: Katılım tarihçesi görebilir
     * 
     * OTOMATIK VERİ:
     * Genelde sistem otomatik olarak:
     * - Etkinlik başlangıç saati geçince -> STARTED
     * - Etkinlik bitiş saati geçince -> ENDED
     * 
     * MANUEL OPERASYONlarını:
     * 1. Admin: "Etkinliği Kapat" (erken kapama)
     * 2. Event::find($id)->update(['status' => EventStatus::ENDED])
     * 3. Status: PUBLISHED -> ENDED
     * 4. ended_at: now()
     * 
     * İŞLEM AKIŞI:
     * 1. Etkinlik bitişi
     * 2. Final check-in count
     * 3. Revenue calculation
     * 4. Report generation
     * 5. Archive: ENDED status
     * 6. Email: Final statistics (organizatöre)
     * 
     * GÖRÜNÜRLÜK:
     * - Event::visibleTo(Admin) -> Tümü görür (ENDED'lar da)
     * - Event::visibleTo(Organizer) -> Kendi ENDED'ları
     * - Event::visibleTo(Attendee) -> ENDED'lar gizli (history'de görebilir)
     * 
     * DATABASE:
     * events.status = 'ended'
     * events.ended_at = 2026-02-15 20:00:00
     * 
     * QUERY:
     * $past = Event::where('status', EventStatus::ENDED)->get();
     * $thisMonth = Event::where('status', EventStatus::ENDED)
     *     ->whereMonth('ended_at', now()->month)
     *     ->get();
     * 
     * RAPORLAMA:
     * - Kaç etkinlik tamamlandı?
     * - Ortalama katılım sayısı?
     * - Toplam gelir?
     * - Best performing events
     * - Customer feedback
     * 
     * OPTIMIZATION:
     * ENDED etkinlikler SEARCH'ten çıkartılabilir:
     * SELECT * FROM events WHERE status != 'ended'
     * 
     * HISTORICAL DATA:
     * - Orders, Tickets, Check-ins: Saklanır
     * - Referenz data: Arşive alınabilir
     * - Billing: Final olur
     */
    case ENDED = 'ended';

    /**
     * ============================================================
     * ETKİNLİK DURUMU AKIŞ TABLOSU
     * ============================================================
     * 
     * NORMAL FLOW:
     * DRAFT ──[Yayın Yap]──> PUBLISHED ──[Bitiş Saati]──> ENDED
     * 
     * ERKEN KAPAMA:
     * PUBLISHED ──[Admin: Kapat]──> ENDED
     * 
     * CANCELLED (opsiyonal):
     * DRAFT ──[İptal]──> (Silinir, CANCELLED state yok ama)
     * PUBLISHED ──[İptal]──> (Silinir veya DRAFT'a dönür)
     * 
     * TIMING:
     * DRAFT: Organizatör hazırlanırken (saatler/günler)
     * PUBLISHED: Satış dönemi (günler/haftalar/aylar)
     * ENDED: Arşiv (süresiz)
     * 
     * ============================================================
     * ROLE-BASED ACCESS & VISIBILITY
     * ============================================================
     * 
     * ADMIN:
     * Görünürlük: DRAFT, PUBLISHED, ENDED tümü
     * İşlemler: Herşey yapabilir
     * 
     * ORGANIZER:
     * Görünürlük: Kendi DRAFT/PUBLISHED/ENDED'ları
     * İşlemler: DRAFT->PUBLISHED, PUBLISHED->ENDED
     * 
     * ATTENDEE:
     * Görünürlük: PUBLISHED (ve kendi ENDED'ları)
     * İşlemler: Sadece satın alma
     * 
     * SCOPE EXAMPLE (Event Model):
     * public function scopeVisibleTo(Builder $q, User $user): void
     * {
     *     match($user->role) {
     *         UserRole::ADMIN => 
     *             // All statuses
     *         ,
     *         UserRole::ORGANIZER => 
     *             $q->where('organizer_id', $user->id)
     *         ,
     *         UserRole::ATTENDEE => 
     *             $q->where('status', EventStatus::PUBLISHED)
     *                 ->orWhereHas('orders', 
     *                     fn($q) => $q->where('user_id', $user->id))
     *         ,
     *     };
     * }
     * 
     * ============================================================
     * QUERY PATTERNS
     * ============================================================
     * 
     * Upcoming events (Yaklaşan etkinlikler):
     * $upcoming = Event::published()
     *     ->where('start_time', '>', now())
     *     ->where('start_time', '<', now()->addDays(30))
     *     ->get();
     * 
     * Recent finished (Yeni tamamlanan):
     * $recent = Event::where('status', EventStatus::ENDED)
     *     ->where('ended_at', '>', now()->subDays(7))
     *     ->get();
     * 
     * Popular (Popüler etkinlikler):
     * $popular = Event::published()
     *     ->withCount('orders')
     *     ->orderByDesc('orders_count')
     *     ->limit(10)
     *     ->get();
     */
}

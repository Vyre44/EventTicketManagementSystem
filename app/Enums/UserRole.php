<?php

namespace App\Enums;

/**
 * UserRole Enum - PHP 8.1 Backed Enum
 * 
 * Type-safe role management: ADMIN, ORGANIZER, ATTENDEE
 * Laravel casting: 'role' => UserRole::class (Model)
 * tryFrom() ile string -> enum conversion
 * cases() ile tüm enum listesi
 */
enum UserRole: string
{
    /**
     * ADMIN ROL
     * 
     * KİM?
     * - Sistem yöneticisi
     * - Platformu kontrol eder
     * - Tüm kaynakları yönetir
     * 
     * NE YAPABILIR?
     * - Tüm etkinlikleri görebilir (DRAFT, PUBLISHED)
     * - Herhangi bir organizatörün biletlerini kontrol edebilir
     * - Check-in işlemlerini geri alabilir (undo)
     * - Bileti iptal edebilir (cancel)
     * - Raporlar oluşturabilir
     * - Sistem istatistiklerini görebilir
     * - AUTHORIZATION: RoleMiddleware'de kontrol edilir
     * 
     * DATABASE:
     * Veritabanında: 'admin' (string olarak)
     * User.role column'ında: 'admin'
     * 
     * CAST AYARLARI:
     * User Model'de: 'role' => UserRole::class
     * Otomatik conversion: 'admin' -> UserRole::ADMIN
     * 
     * ÖRNEK KULLANIM:
     * if ($user->role === UserRole::ADMIN) {
     *     // Admin sayfasına erişim
     * }
     */
    case ADMIN = 'admin';

    /**
     * ORGANIZER ROL
     * 
     * KİM?
     * - Etkinlik düzenleyici
     * - Kendi etkinliklerini oluşturur
     * - İşletişim sorumlusu
     * 
     * NE YAPABILIR?
     * - Kendi etkinliklerini oluşturabilir
     * - Kendi etkinliklerinin biletlerini yönetebilir
     * - Check-in işlemlerini yapabilir
     * - Bilet iptal edebilir (sadece kendi etkinlikleri)
     * - Satış raporları görebilir
     * - SADECE kendi etkinlik verilerine erişir
     * - AUTHORIZATION: Routes'da 'event.owner' middleware
     * 
     * VERİTABANI:
     * Veritabanında: 'organizer' (string olarak)
     * User.role column'ında: 'organizer'
     * Event.organizer_id: Bu org'anizatörün ID'si
     * 
     * CAST AYARLARI:
     * User Model'de: 'role' => UserRole::class
     * Otomatik conversion: 'organizer' -> UserRole::ORGANIZER
     * 
     * ÖRNEK KULLANIM:
     * if ($user->role === UserRole::ORGANIZER) {
     *     $events = $user->events()->get();
     * }
     * 
     * AYRIŞIM:
     * - Admin: Tüm etkinlikleri görebilir
     * - Organizer: SADECE $user->events() (auth()->id() eşleşmesi)
     */
    case ORGANIZER = 'organizer';

    /**
     * ATTENDEE ROL
     * 
     * KİM?
     * - Etkinliğe katılmak isteyen kişi
     * - Bilet satın alır
     * - Etkinliğe katılır
     * 
     * NE YAPABILIR?
     * - Yayınlanan etkinlikleri görebilir
     * - Bilet satın alabilir
     * - Kendi bileti görebilir
     * - Check-in noktasında biletini taratabilir
     * - Geçmiş biletlerini görebilir
     * - SADECE public/published etkinliklere erişir
     * - SADECE kendi biletlerini görür
     * 
     * VERİTABANI:
     * Veritabanında: 'attendee' (string olarak)
     * User.role column'ında: 'attendee'
     * Order.user_id: Bu katılımcının sipariş'leri
     * 
     * CAST AYARLARI:
     * User Model'de: 'role' => UserRole::class
     * Otomatik conversion: 'attendee' -> UserRole::ATTENDEE
     * 
     * ÖRNEK KULLANIM:
     * if ($user->role === UserRole::ATTENDEE) {
     *     $tickets = $user->orders()
     *         ->with('event')
     *         ->get();
     * }
     * 
     * SCOPE AYARLARI:
     * - Event::visibleTo($user) -> Published only
     * - Order::where('user_id', $user->id)
     * - Ticket::whereHas('order', fn($q) => $q->where('user_id', $user->id))
     */
    case ATTENDEE = 'attendee';

    /**
     * ============================================================
     * HELPER METHOD'LAR (Extend edilebilir)
     * ============================================================
     * 
     * AÇIKLAMA:
     * Enum'a custom method'lar eklenebilir (PHP 8.1+)
     * 
     * ÖRNEK EXTENSION (Eğer gerekirse):
     * 
     * public function label(): string {
     *     return match($this) {
     *         self::ADMIN => 'Yönetici',
     *         self::ORGANIZER => 'Organizatör',
     *         self::ATTENDEE => 'Katılımcı',
     *     };
     * }
     * 
     * public function canManageAllEvents(): bool {
     *     return $this === self::ADMIN;
     * }
     * 
     * public function canPurchaseTickets(): bool {
     *     return $this === self::ATTENDEE;
     * }
     * 
     * KULLANIM:
     * echo UserRole::ADMIN->label();  // "Yönetici"
     * if (UserRole::ADMIN->canManageAllEvents()) { ... }
     */
}

<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User Model
 * 
 * Authenticatable base class ile Laravel auth sistemi entegrasyonu
 * UserRole enum casting ile tip güvenli rol yönetimi
 * HasMany relationship: events (organizer_id foreign key)
 */
class User extends Authenticatable
{
    // HasFactory: Testing için factory pattern
    // Notifiable: Laravel notification system integration
    use HasFactory, Notifiable;

    // Mass assignment protection
    protected $fillable = ['name', 'email', 'password', 'role'];

    // JSON serialization'da gizlenen alanlar
    protected $hidden = ['password', 'remember_token'];

    /**
     * Attribute casting - Laravel'in otomatik tip dönüşümü
     * datetime: Carbon instance
     * hashed: Bcrypt ile otomatik hashing
     * UserRole::class: Backed enum casting (PHP 8.1)
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * ============================================================
     * İLİŞKİLER (RELATIONSHIPS)
     * ============================================================
     */

    /**
     * BİR-ÇOKLU İLİŞKİ: Kullanıcının Etkinlikleri
     * 
     * Açıklama:
     * - Bir kullanıcı (organizer) birçok etkinliği sahip olabilir
     * - Etkinlik'in 'organizer_id' sütunu User'ın 'id'si ile eşleşir
     * 
     * @return HasMany
     * 
     * KULLANIM:
     * $user = User::find(1);
     * $events = $user->events; // Tüm etkinlikleri al
     * $events = $user->events()->where('status', 'published')->get();
     * 
     * SADECE ORGANIZER'LER İÇİN ANLAM:
     * - Admin: Tüm etkinlikleri yönetiyor olsa da, 'organizer_id' kendilerine ait değildir
     * - Attendee: Hiç etkinlik oluşturmaz
     */
    public function events(): HasMany
    {
        /**
         * hasMany($relatedModel, $foreignKey, $localKey)
         * 
         * - Event modeli: İlişkili model
         * - 'organizer_id': Event'te hangi sütun eşleştiriyor
         * - (Varsayılan) 'id': User'ın hangi sütunu eşleştiriyor
         * 
         * SQL Equivalent:
         * SELECT * FROM events WHERE organizer_id = {$this->id}
         */
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * BİR-ÇOKLU İLİŞKİ: Kullanıcının Siparişleri (Attendee)
     * 
     * Açıklama:
     * - Bir kullanıcı (attendee) birçok sipariş oluşturabilir
     * - Order'ın 'user_id' sütunu User'ın 'id'si ile eşleşir
     * 
     * @return HasMany
     * 
     * KULLANIM:
     * $user = User::find(1);
     * $orders = $user->orders; // Tüm siparişleri al
     * $orders = $user->orders()->where('status', OrderStatus::PAID)->get();
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * ============================================================
     * HELPER METHODS - YARDIMCI METODLAR
     * ============================================================
     */

    /**
     * Kullanıcı Admin mi?
     * 
     * AMAC:
     * Kolay kontrol için yardımcı method
     * 
     * @return bool True eğer admin ise, false değilse
     * 
     * KULLANIM:
     * if ($user->isAdmin()) {
     *     // Admin işlemi yap
     * }
     * 
     * NEDEN HELPER?
     * - Daha okunabilir kod
     * - Enum dönüşümü otomatik
     * - DRY prensibi (tekrar işlemleri azalt)
     */
    public function isAdmin(): bool
    {
        /**
         * Role kontrolü
         * 
         * $this->role:
         * - UserRole enum instance
         * - Örnek: UserRole::ADMIN
         * 
         * UserRole::ADMIN->value:
         * - Enum'ın string değeri
         * - Örnek: 'admin'
         * 
         */
        return ($this->role instanceof \BackedEnum ? $this->role->value : (string) $this->role) === UserRole::ADMIN->value;
    }
}

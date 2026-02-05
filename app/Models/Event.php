<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Event Model
 * 
 * BelongsTo: organizer (User)
 * HasMany: ticketTypes, orders
 * EventStatus enum casting ile status yönetimi
 * Query Scope: visibleTo($user) - role-based filtering
 */
class Event extends Model
{
    /**
     * Database factory desteği - Test için kullanılan factory
     */
    use HasFactory;

    /**
     * ============================================================
     * İLİŞKİLER (RELATIONSHIPS)
     * ============================================================
     */

    /**
     * Etkinliği Oluşturan Organizatör
     * 
     * AÇIKLAMA:
     * Her etkinlik sadece bir organizer tarafından oluşturulur.
     * Etkinlik'in 'organizer_id' User'ın 'id'si ile eşleşir.
     * 
     * @return BelongsTo
     * 
     * KULLANIM:
     * $event = Event::find(1);
     * $organizer = $event->organizer; // User nesnesi
     * echo $organizer->name; // "John Doe"
     * 
     * KORUMA:
     * - Sadece organizer ve admin etkinliği güncelleyebilir
     * - Attendee'ler sadece bilgi görebilir
     */
    public function organizer(): BelongsTo 
    {
        /**
         * belongsTo($relatedModel, $foreignKey, $ownerKey)
         * 
         * - User modeli: İlişkili model
         * - 'organizer_id': Bu tablodaki foreign key
         * - 'id' (default): User tablodaki primary key
         * 
         * SQL Equivalent:
         * SELECT * FROM users WHERE id = events.organizer_id
         */
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Etkinliğin Bilet Türleri
     * 
     * AÇIKLAMA:
     * Her etkinliğin birden fazla bilet türü olabilir.
     * Örnek: VIP, Standart, Ekonomik
     * 
     * @return HasMany
     * 
     * KULLANIM:
     * $event = Event::find(1);
     * $ticketTypes = $event->ticketTypes; // TicketType koleksiyonu
     * 
     * foreach ($event->ticketTypes as $type) {
     *     echo $type->name . " - " . $type->price;
     * }
     */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    /**
     * Etkinliye Ait Siparişler
     * 
     * AÇIKLAMA:
     * Katılımcıların bu etkinlik için yaptığı siparişlerin listesi
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * ============================================================
     * QUERY SCOPES - SORGU KAPSAYICILAR
     * ============================================================
     * 
     * Scopes: Tekrar eden sorguları basitleştirmek için yardımcı metodlar
     * Kullanım: Model::methodName()->get()
     */

    /**
     * Kullanıcı Bazlı Etkinlik Görünürlüğü Filtresi
     * 
     * AÇIKLAMA:
     * Kullanıcının rolüne göre farklı etkinlikleri göster
     * 
     * KURALLAR:
     * - ADMIN: Tüm etkinlikleri görebilir (filter yok)
     * - ORGANIZER: Sadece kendi etkinliklerini görebilir
     * - ATTENDEE: Sadece PUBLISHED etkinlikleri görebilir
     * 
     * KULLANIM:
     * // Giriş yapmış kullanıcı için
     * $events = Event::visibleTo(auth()->user())->get();
     * 
     * // Belirli kullanıcı için
     * $events = Event::visibleTo($user)->get();
     * 
     * @param Builder $query
     * @param User $user - Hangi kullanıcı için filtreleme yapılacak
     * @return Builder - Sorgular chain yapılabilir
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        /**
         * ADIM 1: Kullanıcı rolünü al
         * 
         * UserRole enum'ı string değere çevir
         * (UserRole::ADMIN -> 'admin')
         */
        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        /**
         * ADIM 2: Rolü kontrol et
         */

        // ADMIN: Filtre yok, tüm etkinlikleri göster
        if ($role === UserRole::ADMIN->value) {
            return $query;
        }

        // ORGANIZER: Sadece kendi etkinliklerini göster
        if ($role === UserRole::ORGANIZER->value) {
            return $query->where('organizer_id', $user->id);
        }

        // ATTENDEE: Sadece yayınlanmış etkinlikleri göster
        // (Taslak ve iptal edilen etkinlikler gösterilmez)
        return $query->where('status', EventStatus::PUBLISHED);
    }

    /**
     * ============================================================
     * MASS ASSIGNMENT VE CASTING
     * ============================================================
     */

    /**
     * MASS ASSIGNMENT - Toplu Atama
     * 
     * Bu alanlar Event::create() veya $event->update() ile atanabilir
     * Güvenlik: Başka alanlar atanmaz
     */
    protected $fillable = [
        // Etkinliği oluşturan user ID
        'organizer_id',
        
        // Etkinlik adı
        'title',
        
        // Etkinlik açıklaması
        'description',
        
        // Başlama tarihi ve saati
        'start_time',
        
        // Bitiş tarihi ve saati
        'end_time',
        
        // Kapak resmi dosya yolu
        'cover_image_path',
        
        // Etkinlik durumu (draft, published, cancelled)
        'status',
    ];

    /**
     * CASTING - Otomatik Tip Dönüşümü
     * 
     * Veritabanından alınan verileri otomatik tip dönüştür
     */
    protected function casts(): array
    {
        return [
            /**
             * start_time: Timestamp -> Carbon DateTime object
             * 
             * Veritabanında: '2026-02-15 10:00:00' (string)
             * Kodda: Carbon instance (tarih işlemleri yapılabilir)
             * 
             * Örnek:
             * $event->start_time->format('d.m.Y')
             * $event->start_time->diffForHumans() // "5 days from now"
             */
            'start_time' => 'datetime',
            
            /**
             * end_time: Timestamp -> Carbon DateTime object
             */
            'end_time' => 'datetime',
            
            /**
             * status: String -> EventStatus Enum
             * 
             * Veritabanında: 'published' (string)
             * Kodda: EventStatus::PUBLISHED (enum instance)
             * 
             * Faydaları:
             * - Sadece 3 durum mümkün
             * - IDE autocomplete
             * - Strict comparison
             */
            'status' => EventStatus::class,
        ];
    }

    /**
     * ============================================================
     * ACCESSOR - HESAPLANMIŞ ÖZELLIKLER
     * ============================================================
     * 
     * Accessor: Model'den $model->property şeklinde erişince
     * otomatik olarak hesaplanan veya işlenen değer döner
     */

    /**
     * Kapak Resmi URL'sini Döndür
     * 
     * AÇIKLAMA:
     * Dosya yolundan tam URL oluştur
     * 
     * KULLANIM:
     * $url = $event->cover_image_url;
     * // Sonuç: https://example.com/storage/events/cover.jpg
     * 
     * @return string|null
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        /**
         * cover_image_path varsa:
         * asset('storage/' . path) -> Tam URL
         * 
         * Örneğin:
         * cover_image_path = 'events/123/cover.jpg'
         * Result = 'https://example.com/storage/events/123/cover.jpg'
         * 
         * asset() helper: Laravel'in asset URL oluşturucu
         */
        return $this->cover_image_path 
            ? asset('storage/' . $this->cover_image_path)
            : null;
    }
}

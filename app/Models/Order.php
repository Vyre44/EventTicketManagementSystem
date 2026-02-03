<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;

/**
 * Order Model
 * 
 * BelongsTo: user, event
 * HasMany: tickets
 * OrderStatus enum: PENDING -> PAID -> CANCELLED/REFUNDED
 * Purchase workflow: buy() -> pay() ile status transitions
 */
class Order extends Model
{
    /**
     * Database factory desteği - Test için kullanılan factory
     */
    use HasFactory;

    /**
     * ============================================================
     * MASS ASSIGNMENT - Toplu Atama
     * ============================================================
     * 
     * Bu alanlar Order::create() veya $order->update() ile atanabilir
     * Güvenlik: Başka alanlar atanmaz
     */
    protected $fillable = [
        // Sipariş veren kullanıcı ID
        'user_id',
        
        // Etkinlik ID
        'event_id',
        
        // Toplam ödeme tutarı (TL, EUR, USD vb.)
        'total_amount',
        
        // Sipariş durumu: pending, paid, cancelled, refunded
        'status',
        
        // Ödemenin yapıldığı tarih/saat
        'paid_at',
    ];

    /**
     * ============================================================
     * CASTING - Otomatik Tip Dönüşümü
     * ============================================================
     */
    protected $casts = [
        /**
         * paid_at: Timestamp -> Carbon DateTime object
         * 
         * Veritabanında: '2026-02-15 10:30:00' (string/timestamp)
         * Kodda: Carbon instance (tarih işlemleri yapılabilir)
         * 
         * Örnek:
         * if ($order->paid_at) {
         *     echo "Ödeme tarihi: " . $order->paid_at->format('d.m.Y H:i');
         * }
         */
        'paid_at' => 'datetime',
        
        /**
         * status: String -> OrderStatus Enum
         * 
         * Veritabanında: 'pending' (string)
         * Kodda: OrderStatus::PENDING (enum instance)
         */
        'status' => OrderStatus::class,
    ];

    /**
     * ============================================================
     * İLİŞKİLER (RELATIONSHIPS)
     * ============================================================
     */

    /**
     * Sipariş Veren Kullanıcı
     * 
     * AÇIKLAMA:
     * Bir sipariş sadece bir kullanıcı tarafından verilir.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 
     * KULLANIM:
     * $order = Order::find(1);
     * $user = $order->user; // User nesnesi
     * echo $user->name; // "John Doe"
     * echo $user->email; // "john@example.com"
     */
    public function user()
    {
        /**
         * belongsTo($relatedModel, $foreignKey, $ownerKey)
         * 
         * - User modeli: İlişkili model
         * - 'user_id': Bu tablodaki foreign key
         * - 'id' (default): User tablodaki primary key
         * 
         * SQL Equivalent:
         * SELECT * FROM users WHERE id = orders.user_id
         */
        return $this->belongsTo(User::class);
    }

    /**
     * Siparişin Ait Olduğu Etkinlik
     * 
     * AÇIKLAMA:
     * Bir sipariş belirli bir etkinliğe aittir.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 
     * KULLANIM:
     * $order = Order::find(1);
     * $event = $order->event; // Event nesnesi
     * echo $event->title; // "Tech Konferansı"
     * echo $event->start_time; // "2026-02-15 10:00:00"
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Siparişe Ait Biletler
     * 
     * AÇIKLAMA:
     * Bir siparışte birden fazla bilet olabilir.
     * Örneğin: 3 bilet satın aldığında 3 Ticket nesnesi oluşur
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 
     * KULLANIM:
     * $order = Order::find(1);
     * $tickets = $order->tickets; // Tickets koleksiyonu
     * 
     * foreach ($order->tickets as $ticket) {
     *     echo "Bilet: " . $ticket->code;
     *     echo "Durum: " . $ticket->status->value;
     * }
     * 
     * ÖRNEK SENARYOLAR:
     * - 1 bilet satın alındı: 1 Ticket
     * - 3 bilet satın alındı: 3 Ticket
     * - Her bilet ACTIVE -> CHECKED_IN'e dönüşebilir
     */
    public function tickets()
    {
        /**
         * hasMany($relatedModel, $foreignKey, $localKey)
         * 
         * - Ticket modeli: İlişkili model
         * - 'order_id': Ticket'te hangi sütun eşleştiriyor
         * - 'id' (default): Order'in hangi sütunu eşleştiriyor
         * 
         * SQL Equivalent:
         * SELECT * FROM tickets WHERE order_id = {$this->id}
         */
        return $this->hasMany(Ticket::class);
    }
}

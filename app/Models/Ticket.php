<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TicketStatus;

/**
 * Ticket Model
 * 
 * BelongsTo: order, ticketType
 * TicketStatus enum: ACTIVE -> CHECKED_IN -> CANCELLED/REFUNDED
 * Custom method: checkIn() - status transition logic
 */
class Ticket extends Model
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
     * Bu alanlar Ticket::create() veya $ticket->update() ile atanabilir
     * Güvenlik: Başka alanlar atanmaz
     */
    protected $fillable = [
        // Bu bilet hangi sipariş için? (foreign key)
        'order_id',
        
        // Bu bilet hangi türde? (foreign key)
        'ticket_type_id',
        
        // Bilet kodu (örn: QR code, barcode)
        'code',
        
        // Bilet durumu: active, checked_in, cancelled, refunded
        'status',
        
        // Check-in yapıldığı tarih/saat
        'checked_in_at',
    ];

    /**
     * ============================================================
     * CASTING - Otomatik Tip Dönüşümü
     * ============================================================
     */
    protected $casts = [
        /**
         * checked_in_at: Timestamp -> Carbon DateTime object
         * 
         * Veritabanında: '2026-02-15 15:30:00' (string/timestamp)
         * Kodda: Carbon instance (tarih işlemleri yapılabilir)
         * 
         * Örnek:
         * $ticket->checked_in_at->format('H:i')
         * $ticket->checked_in_at->diffForHumans()
         */
        'checked_in_at' => 'datetime',
        
        /**
         * status: String -> TicketStatus Enum
         * 
         * Veritabanında: 'active' (string)
         * Kodda: TicketStatus::ACTIVE (enum instance)
         */
        'status' => TicketStatus::class,
    ];

    /**
     * ============================================================
     * İLİŞKİLER (RELATIONSHIPS)
     * ============================================================
     */

    /**
     * Bu Bilet Hangi Siparişe Ait?
     * 
     * AÇIKLAMA:
     * Bir bilet sadece bir siparişe aittir.
     * Sipariş ile bilet arasında bağlantı
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 
     * KULLANIM:
     * $ticket = Ticket::find(1);
     * $order = $ticket->order; // Order nesnesi
     * echo $order->total_amount; // Sipariş tutarı
     */
    public function order()
    {
        /**
         * belongsTo($relatedModel, $foreignKey, $ownerKey)
         * 
         * - Order modeli: İlişkili model
         * - 'order_id': Bu tablodaki foreign key
         * - 'id' (default): Order tablodaki primary key
         * 
         * SQL Equivalent:
         * SELECT * FROM orders WHERE id = tickets.order_id
         */
        return $this->belongsTo(Order::class);
    }

    /**
     * Bu Bilet Hangi Türde?
     * 
     * AÇIKLAMA:
     * Bir bilet belirli bir bilet türüne (VIP, Standart vb.) aittir.
     * Bilet türü ile fiyat, ad gibi bilgiler gelir
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 
     * KULLANIM:
     * $ticket = Ticket::find(1);
     * $type = $ticket->ticketType; // TicketType nesnesi
     * echo $type->name; // "VIP"
     * echo $type->price; // 500
     */
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    /**
     * ============================================================
     * BUSINESS LOGIC - METOD KULLAN
     * ============================================================
     */

    /**
     * Bileta Check-in Yap
     * 
     * AÇIKLAMA:
     * Katılımcı etkinliğe girdiğinde bileti kullanmak için çağrılır.
     * 
     * KURALLAR:
     * - Sadece ACTIVE biletler check-in edilebilir
     * - Status: ACTIVE -> CHECKED_IN
     * - checked_in_at alanı güncellenir
     * 
     * @return bool Check-in başarılı mı?
     * 
     * KULLANIM:
     * if ($ticket->checkIn()) {
     *     echo "Check-in yapıldı!";
     * } else {
     *     echo "Bu bilet check-in yapılamıyor";
     * }
     * 
     * ÖRNEK SCENARIO:
     * - Bilet ACTIVE ise: true döner, check-in yapılır
     * - Bilet CHECKED_IN ise: false döner (zaten check-in'di)
     * - Bilet CANCELLED ise: false döner (iptal bilet)
     */
    public function checkIn()
    {
        /**
         * ADIM 1: Check-in yapılabilir mi?
         * 
         * Sadece ACTIVE biletler check-in yapılabilir
         * CHECKED_IN, CANCELLED, REFUNDED biletler check-in yapılamaz
         */
        if ($this->status !== TicketStatus::ACTIVE) {
            return false;
        }

        /**
         * ADIM 2: Status'u CHECKED_IN'e çevir
         */
        $this->status = TicketStatus::CHECKED_IN;
        
        /**
         * ADIM 3: Check-in zamanını kaydet
         * 
         * now(): Şu anki tarih/saat (Carbon instance)
         */
        $this->checked_in_at = now();
        
        /**
         * ADIM 4: Veritabanına kaydet
         */
        $this->save();
        
        /**
         * ADIM 5: Başarı döner
         */
        return true;
    }
}

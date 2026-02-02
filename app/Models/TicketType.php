<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TicketType Model
 *
 * Bir etkinlikteki bilet türlerini (ör: VIP, Standart) temsil eder.
 * Her TicketType bir Event'e aittir ve birden fazla Ticket ile ilişkilidir.
 *
 * Alanlar:
 * - event_id: Bağlı olduğu etkinlik
 * - name: Bilet tipi adı
 * - price: Bilet fiyatı
 * - total_quantity: Toplam stok
 * - remaining_quantity: Kalan stok
 * - sale_start/sale_end: Satış aralığı
 *
 * İlişkiler:
 * - event(): TicketType -> Event
 * - tickets(): TicketType -> Ticket (çoklu)
 */
class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'total_quantity',
        'remaining_quantity',
        'sale_start',
        'sale_end',
    ];

        protected $casts = [
        'sale_start' => 'datetime',
        'sale_end' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        /**
         * TicketType'ın ait olduğu Event ilişkisi
         */
        return $this->belongsTo(Event::class);
    }

    public function tickets()
    {
        /**
         * TicketType'ın sahip olduğu Ticket ilişkisi
         */
        return $this->hasMany(Ticket::class);
    }
}

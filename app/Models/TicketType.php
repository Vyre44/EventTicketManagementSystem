<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TicketType Model
 * 
 * BelongsTo: event
 * HasMany: tickets
 * Inventory management: total_quantity, remaining_quantity
 * Time-based constraints: sale_start, sale_end
 */
class TicketType extends Model
{
    use HasFactory;

    // Mass assignment protection
    protected $fillable = [
        'event_id', 'name', 'price', 
        'total_quantity', 'remaining_quantity',
        'sale_start', 'sale_end',
    ];

    /**
     * CASTING AYARLARI
     * 
     * cast: Database değerini otomatik PHP türüne dönüştür
     * 
     * AÇIKLAMA:
     * Database'de: sale_start = "2026-01-01 10:00:00" (string)
     * Laravel'de: $ticketType->sale_start = Carbon object
     * 
     * AVANTAJLAR:
     * - Date/time operasyonları kolay
     * - Comparisons: $ticketType->sale_start->isFuture()
     * - Formatting: $ticketType->sale_start->format('d.m.Y')
     * - Timezones: Otomatik dönüşüm
     */
    protected $casts = [
        'sale_start' => 'datetime',  // String -> Carbon\Carbon
        'sale_end' => 'datetime',    // String -> Carbon\Carbon
        'is_active' => 'boolean',    // String/int -> bool
    ];

    /**
     * ============================================================
     * İLİŞKİLER (Relationships)
     * ============================================================
     */

    /**
     * ETKİNLİĞE AİT OLMAK (BelongsTo)
     * 
     * AÇIKLAMA:
     * Herbir TicketType bir Event'e aittir
     * Inverse: Event hasMany TicketType
     * 
     * DATABASE:
     * ticket_types.event_id -> events.id
     * 
     * KULLANIM:
     * $ticketType->event  // Event model'ini döner
     * $ticketType->event->name  // "Konser 2026"
     * 
     * EAGER LOADING:
     * TicketType::with('event')->get()  // N+1 sorgu avoid
     * 
     * CONSTRAINT:
     * TicketType::whereHas('event', fn($q) => 
     *     $q->where('status', 'published')
     * )->get();  // Sadece yayında etkinliklerin tipleri
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * BİLETLERİ SAHIP OLMAK (HasMany)
     * 
     * AÇIKLAMA:
     * Herbir TicketType birden fazla Ticket'e sahip
     * Inverse: Ticket belongsTo TicketType
     * 
     * DATABASE:
     * tickets.ticket_type_id -> ticket_types.id
     * 
     * KULLANIM:
     * $ticketType->tickets  // Collection of Ticket models
     * $ticketType->tickets->count()  // Kaç bilet satıldı?
     * 
     * ÖRNEK:
     * $vip = $event->ticketTypes()->first();
     * $vipTickets = $vip->tickets;  // VIP biletleri (ACTIVE, CHECKED_IN, CANCELLED, REFUNDED)
     * 
     * FİLTRELEME:
     * $vip->tickets()
     *     ->where('status', TicketStatus::ACTIVE)
     *     ->count();  // Kaç VIP bilet henüz satılmamış?
     * 
     * EAGER LOADING:
     * TicketType::with('tickets')->get()  // N+1 avoid
     * 
     * COUNTING:
     * TicketType::withCount('tickets')->get()
     * // $ticketType->tickets_count
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * ============================================================
     * HELPER METHOD'LAR (Opsiyonal, gerekirse extend edilir)
     * ============================================================
     * 
     * AÇIKLAMA:
     * Sık yapılan operasyonlar için utility method'lar
     * 
     * ÖRNEK METHOD'LAR (eklenebilir):
     * 
     * public function isSalesOpen(): bool
     * {
     *     now between sale_start and sale_end mı?
     *     return now() >= $this->sale_start && now() <= $this->sale_end;
     * }
     * 
     * public function getSoldCount(): int
     * {
     *     Kaç bilet satıldı? (ACTIVE + CHECKED_IN + REFUNDED)
     *     return $this->tickets()
     *         ->whereIn('status', [
     *             TicketStatus::ACTIVE,
     *             TicketStatus::CHECKED_IN,
     *             TicketStatus::REFUNDED
     *         ])
     *         ->count();
     * }
     * 
     * public function getAvailableCount(): int
     * {
     *     Kaç bilet daha satılabilir?
     *     return max(0, $this->total_quantity - $this->getSoldCount());
     * }
     * 
     * public function decrementRemaining(): self
     * {
     *     Stok azalt (bilet satıldığında)
     *     $this->decrement('remaining_quantity');
     *     return $this;
     * }
     * 
     * KULLANIM:
     * if ($ticketType->isSalesOpen()) {
     *     // Satış aralığı içinde
     * }
     * 
     * $available = $ticketType->getAvailableCount();
     * if ($available == 0) {
     *     // Tükendi! Satın alma blokla
     * }
     */
}

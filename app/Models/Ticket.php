<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TicketStatus;

// Ticket: Satın alınan bir bileti temsil eder.
// - Her ticket bir order ve bir ticket_type ile ilişkilidir.
// - Status: active, checked_in, cancelled, refunded
// - checked_in_at: biletin check-in yapıldığı zamanı tutar.
class Ticket extends Model
{
    use HasFactory;

    // Mass assignment için izin verilen alanlar.
    protected $fillable = [
        'order_id',
        'ticket_type_id',
        'code',
        'status', // active, checked_in, cancelled, refunded
        'checked_in_at',
    ];

    // checked_in_at alanı otomatik olarak datetime olarak cast edilir.
    protected $casts = [
        'checked_in_at' => 'datetime',
        'status' => TicketStatus::class,
    ];

    // Biletin ait olduğu sipariş ile ilişki.
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Bileti check-in yapar. Sadece status 'active' ise check-in olur. checked_in_at güncellenir.
    public function checkIn()
    {
        if ($this->status !== TicketStatus::ACTIVE) {
            return false;
        }
        $this->status = TicketStatus::CHECKED_IN;
        $this->checked_in_at = now();
        $this->save();
        return true;
    }

    // Biletin ait olduğu ticket type ile ilişki.
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }
}

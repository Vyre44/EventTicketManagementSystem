<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;

// Order: Bir kullanıcının bir etkinlik için yaptığı bilet satın alma işlemini temsil eder.
// - Her order bir kullanıcıya ve bir etkinliğe bağlıdır.
// - Bir order'ın birden fazla bileti (ticket) olabilir.
// - Status: pending, paid, cancelled, failed, expired, refunded
// - paid_at: ödemenin yapıldığı zamanı tutar.
class Order extends Model
{
    use HasFactory;

    // Mass assignment için izin verilen alanlar.
    protected $fillable = [
        'user_id',
        'event_id',
        'total_amount',
        'status', // pending, paid, cancelled, failed, expired, refunded
        'paid_at',
    ];

    // paid_at alanı otomatik olarak datetime olarak cast edilir.
    protected $casts = [
        'paid_at' => 'datetime',
        'status' => OrderStatus::class,
    ];

    // Siparişi yapan kullanıcı ile ilişki.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Siparişin ait olduğu etkinlik ile ilişki.
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Siparişe bağlı biletler ile ilişki.
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}

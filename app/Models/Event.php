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

// Event: Etkinlik modelidir. Etkinliklerin temel bilgilerini ve ilişkilerini tutar.
// - organizer() ile etkinliği oluşturan kullanıcıya erişim sağlar.
// - scopeVisibleTo() ile kullanıcının görebileceği etkinlikleri filtreler.
// - fillable ve casts ile güvenli veri atama ve tip dönüşümü yapılır.
class Event extends Model
{
    use HasFactory;

    // Etkinliği oluşturan kullanıcı (organizer) ile ilişki.
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Kullanıcının görebileceği eventleri filtreler.
     * - Admin tüm eventleri görür.
     * - Organizer sadece kendi eventlerini görür.
     * - Attendee/guest sadece yayınlanan (published) eventleri görür.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->role === UserRole::ADMIN) {
            return $query;
        }

        if ($user->role === UserRole::ORGANIZER) {
            return $query->where('organizer_id', $user->id);
        }

        // Attendee/guest: sadece published eventleri görür
        return $query->where('status', EventStatus::PUBLISHED);
    }

    // Mass assignment için izin verilen alanlar.
    protected $fillable = [
        'organizer_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'cover_path',
        'status',
    ];

    // Otomatik tip dönüşümü ve enum cast işlemleri.
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time'   => 'datetime',
            'status'     => EventStatus::class,
        ];
    }

    /**
     * Event'in sahip olduğu ticket type'lar (hasMany)
     */
    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }
}

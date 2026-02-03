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

/**
 * Event: Etkinlik modelidir. Etkinliklerin temel bilgilerini ve ilişkilerini tutar.
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 */
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
        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        if ($role === UserRole::ADMIN->value) {
            return $query;
        }

        if ($role === UserRole::ORGANIZER->value) {
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
        'cover_image_path',
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

    /**
         * Cover image URL accessor
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image_path 
            ? asset('storage/' . $this->cover_image_path)
            : null;
    }
}

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
 * Sistemdeki kullanıcıları temsil eder. Kullanıcılar attendee, organizer veya admin rolünde olabilir.
 *
 * Alanlar:
 * - name, email, password, role
 *
 * İlişkiler:
 * - events(): Organizer rolündeki kullanıcının sahip olduğu etkinlikler
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        // Kullanıcıya toplu atama yapılabilen alanlar
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        // Dışarıya gösterilmeyecek alanlar
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        /**
         * Alanların otomatik dönüşüm kuralları
         */
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Kullanıcının sahip olduğu etkinlikler (organizer_id üzerinden)
     */
    public function events(): HasMany
    {
        /**
         * Organizer rolündeki kullanıcının sahip olduğu etkinlikler
         */
        return $this->hasMany(Event::class, 'organizer_id');
    }

}

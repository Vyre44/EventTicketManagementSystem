<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Kullanici Yonetimi modulu icin ana model.
 * Kimlik dogrulama, rol bilgisi ve kullaniciya bagli verilerin merkezidir.
 * UserRole enumu ile yetki kontrolu saglanir.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Toplu atama icin izinli alanlar.
     */
    protected $fillable = ['name', 'email', 'password', 'role'];

    /**
     * JSON cikisinda gizlenen alanlar.
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Tip donusumleri: dogrulama zamani, sifre hash'i ve rol enumu.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Organizator rolundeki kullanicinin etkinlikleri.
     * Modul: Etkinlik Yonetimi.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * Attendee rolundeki kullanicinin siparisleri.
     * Modul: Bilet Yonetimi ve satin alma akisi.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Kullanici admin mi kontrolu.
     */
    public function isAdmin(): bool
    {
        return ($this->role instanceof \BackedEnum ? $this->role->value : (string) $this->role) === UserRole::ADMIN->value;
    }
}

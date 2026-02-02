<?php

namespace App\Enums;

/**
 * UserRole: Kullanıcı rolünü temsil eden enum.
 * - ADMIN: Yönetici.
 * - ORGANIZER: Etkinlik düzenleyici.
 * - ATTENDEE: Katılımcı.
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case ORGANIZER = 'organizer';
    case ATTENDEE = 'attendee';
}

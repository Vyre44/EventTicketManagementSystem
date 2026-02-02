<?php

namespace App\Enums;

/**
 * TicketStatus: Biletin durumunu temsil eden enum.
 * - ACTIVE: Satın alındı, kullanılabilir.
 * - CHECKED_IN: Etkinlik girişinde kullanıldı.
 * - CANCELLED: İptal edildi.
 * - REFUNDED: İade edildi.
 */
enum TicketStatus: string
{
    case ACTIVE = 'active';
    case CHECKED_IN = 'checked_in';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
}

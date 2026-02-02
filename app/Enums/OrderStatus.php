<?php

namespace App\Enums;

/**
 * OrderStatus: Siparişin durumunu temsil eden enum.
 * - PENDING: Beklemede, ödeme yapılmadı.
 * - PAID: Ödeme tamamlandı.
 * - CANCELLED: İptal edildi.
 * - REFUNDED: İade edildi.
 */
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
}

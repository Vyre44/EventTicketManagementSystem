<?php

namespace App\Enums;

//Ticket status enum
enum TicketStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case USED = 'used'; 
    case REFUNDED = 'refunded';
}

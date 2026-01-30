<?php

namespace App\Enums;

//User role enum
enum UserRole: string
{
    case ADMIN = 'admin';
    case ORGANIZER = 'organizer';
    case ATTENDEE = 'attendee';
}

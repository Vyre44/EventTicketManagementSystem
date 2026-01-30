<?php

namespace App\Enums;

//Event status enum
enum EventStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
}

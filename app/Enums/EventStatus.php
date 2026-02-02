<?php

namespace App\Enums;

/**
 * EventStatus: Etkinliğin yayın durumunu temsil eden enum.
 * - PUBLISHED: Yayında.
 * - DRAFT: Taslak.
 */
enum EventStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
}

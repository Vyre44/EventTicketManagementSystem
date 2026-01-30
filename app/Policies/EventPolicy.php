<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;

/**
 * EventPolicy
 *
 * Etkinlikler üzerinde rol tabanlı yetki kontrollerini yönetir.
 *
 * - Admin tüm etkinliklerde tam yetkilidir.
 * - Organizer sadece kendi etkinliklerinde işlem yapabilir.
 * - Attendee erişemez.
 *
 * Yöntemler:
 * - viewAny: Listeleme yetkisi
 * - view: Detay görüntüleme yetkisi
 * - create: Oluşturma yetkisi
 * - update: Güncelleme yetkisi
 * - delete: Silme yetkisi
 */
class EventPolicy
{
    private function isAdmin(User $user): bool
    {
        /**
         * Kullanıcı admin mi?
         */
        return $user->role === UserRole::ADMIN;
    }

    private function isOrganizer(User $user): bool
    {
        /**
         * Kullanıcı organizer mı?
         */
        return $user->role === UserRole::ORGANIZER;
    }

    private function ownsEvent(User $user, Event $event): bool
    {
        /**
         * Kullanıcı etkinliğin sahibi mi?
         */
        return $event->organizer_id === $user->id;
    }

    public function viewAny(User $user): bool
    {
        /**
         * Kullanıcı etkinlikleri listeleyebilir mi?
         */
        return $this->isAdmin($user) || $this->isOrganizer($user);
    }

    public function view(User $user, Event $event): bool
    {
        /**
         * Kullanıcı etkinliği görüntüleyebilir mi?
         */
        return $this->isAdmin($user) || ($this->isOrganizer($user) && $this->ownsEvent($user, $event));
    }

    public function create(User $user): bool
    {
        /**
         * Kullanıcı etkinlik oluşturabilir mi?
         */
        return $this->isAdmin($user) || $this->isOrganizer($user);
    }

    public function update(User $user, Event $event): bool
    {
        /**
         * Kullanıcı etkinliği güncelleyebilir mi?
         */
        return $this->isAdmin($user) || ($this->isOrganizer($user) && $this->ownsEvent($user, $event));
    }

    public function delete(User $user, Event $event): bool
    {
        /**
         * Kullanıcı etkinliği silebilir mi?
         */
        return $this->update($user, $event);
    }
}

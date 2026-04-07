<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

/**
 * Authorization policy for Notification resources.
 *
 * SRP: Solely responsible for determining access to notification records.
 * OCP: New notification abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Advanced-staff rule: Only advanced staff may list, view, create, or delete notifications.
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class NotificationPolicy
{
    /**
     * Determines whether the user can list notifications.
     * Only advanced staff may manage the notification system.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can view a specific notification.
     *
     * @param  User          $user
     * @param  Notification  $notification
     * @return bool
     */
    public function view(User $user, Notification $notification): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can create and send a notification.
     * Only advanced staff may send notifications to audiences.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can delete a notification.
     * Only admins may delete notifications (Gate::before handles this).
     *
     * @param  User          $user
     * @param  Notification  $notification
     * @return bool
     */
    public function delete(User $user, Notification $notification): bool
    {
        return false;
    }
}

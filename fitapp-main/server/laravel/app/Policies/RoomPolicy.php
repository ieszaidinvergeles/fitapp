<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

/**
 * Authorization policy for Room resources.
 *
 * SRP: Solely responsible for determining access to room records.
 * OCP: New room abilities are added as methods without modifying existing authorization logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Multi-tenant rule: Advanced staff may only mutate rooms that belong to their own gym.
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class RoomPolicy
{
    /**
     * Determines whether any user can list rooms.
     * Rooms are publicly readable.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific room.
     *
     * @param  User  $user
     * @param  Room  $room
     * @return bool
     */
    public function view(User $user, Room $room): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create a room.
     * Only advanced staff may create rooms within their gym.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can update a room.
     * Advanced staff may only update rooms that belong to their assigned gym.
     *
     * @param  User  $user
     * @param  Room  $room
     * @return bool
     */
    public function update(User $user, Room $room): bool
    {
        return $user->isAdvanced() && $user->current_gym_id === $room->gym_id;
    }

    /**
     * Determines whether the user can delete a room.
     * Admin-only via Gate::before; advanced check is a secondary safeguard.
     *
     * @param  User  $user
     * @param  Room  $room
     * @return bool
     */
    public function delete(User $user, Room $room): bool
    {
        return $user->isAdvanced() && $user->current_gym_id === $room->gym_id;
    }
}

<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

/**
 * Authorization policy for Booking resources.
 *
 * SRP: Solely responsible for determining who can perform actions on bookings.
 * OCP: New booking abilities are added as methods without modifying existing ones.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Ownership rule: A user may only view or cancel their own bookings.
 * Admin rule is intercepted globally via Gate::before in AppServiceProvider.
 */
class BookingPolicy
{
    /**
     * Determines whether any authenticated user can list bookings.
     * Listing scope is narrowed in the controller per role.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific booking.
     * A non-advanced user may only view their own booking.
     *
     * @param  User     $user
     * @param  Booking  $booking
     * @return bool
     */
    public function view(User $user, Booking $booking): bool
    {
        if ($user->canManageOperations()) {
            return true;
        }

        return $user->id === $booking->user_id;
    }

    /**
     * Determines whether the authenticated user can create a booking.
     * Any authenticated user may attempt to book a class (eligibility is checked in the controller).
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can cancel (update) a booking.
     * A non-advanced user may only cancel their own booking.
     *
     * @param  User     $user
     * @param  Booking  $booking
     * @return bool
     */
    public function update(User $user, Booking $booking): bool
    {
        if ($user->canManageOperations()) {
            return true;
        }

        return $user->id === $booking->user_id;
    }

    /**
     * Determines whether the user can hard-delete a booking.
     * Only advanced staff may delete bookings (admin bypass via Gate::before).
     *
     * @param  User     $user
     * @param  Booking  $booking
     * @return bool
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $user->canManageOperations();
    }
}

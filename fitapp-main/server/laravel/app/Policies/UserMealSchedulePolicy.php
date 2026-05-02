<?php

namespace App\Policies;

use App\Models\UserMealSchedule;
use App\Models\User;

/**
 * Authorization policy for UserMealSchedule resources.
 *
 * SRP: Solely responsible for determining access to meal schedule entries.
 * OCP: New meal schedule abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Ownership rule: A user may only manage their own meal schedule.
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class UserMealSchedulePolicy
{
    /**
     * Determines whether the user can list meal schedule entries.
     * Users are scoped to their own entries in the controller.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific meal entry.
     * A user may only view their own meal schedule entries.
     *
     * @param  User               $user
     * @param  UserMealSchedule   $mealSchedule
     * @return bool
     */
    public function view(User $user, UserMealSchedule $mealSchedule): bool
    {
        return $user->id === $mealSchedule->user_id;
    }

    /**
     * Determines whether the user can create a meal schedule entry.
     * Any authenticated user may add to their own meal schedule.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can update a meal entry.
     * A user may only update their own meal entries.
     *
     * @param  User              $user
     * @param  UserMealSchedule  $mealSchedule
     * @return bool
     */
    public function update(User $user, UserMealSchedule $mealSchedule): bool
    {
        return $user->id === $mealSchedule->user_id;
    }

    /**
     * Determines whether the user can delete a meal entry.
     * A user may only delete their own meal entries.
     *
     * @param  User              $user
     * @param  UserMealSchedule  $mealSchedule
     * @return bool
     */
    public function delete(User $user, UserMealSchedule $mealSchedule): bool
    {
        return $user->id === $mealSchedule->user_id;
    }
}

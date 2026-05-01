<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

/**
 * Authorization policy for Exercise resources.
 *
 * SRP: Solely responsible for determining access to exercise records.
 * OCP: New exercise abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Public read rule: Exercises are publicly viewable.
 * Advanced staff may create and update exercises global across the platform.
 * Only admins may delete exercises. Admin bypass is via Gate::before.
 */
class ExercisePolicy
{
    /**
     * Determines whether the user can list exercises.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific exercise.
     *
     * @param  User      $user
     * @param  Exercise  $exercise
     * @return bool
     */
    public function view(User $user, Exercise $exercise): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create an exercise.
     * Only advanced staff may create exercises.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can update an exercise.
     * Only advanced staff may update exercises.
     *
     * @param  User      $user
     * @param  Exercise  $exercise
     * @return bool
     */
    public function update(User $user, Exercise $exercise): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can delete an exercise.
     * Only admins may delete exercises (Gate::before handles this).
     *
     * @param  User      $user
     * @param  Exercise  $exercise
     * @return bool
     */
    public function delete(User $user, Exercise $exercise): bool
    {
        return false;
    }
}

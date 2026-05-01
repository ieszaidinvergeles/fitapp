<?php

namespace App\Policies;

use App\Models\Routine;
use App\Models\User;

/**
 * Authorization policy for Routine resources.
 *
 * SRP: Solely responsible for determining access to routine records.
 * OCP: New routine abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Hybrid rule: Any authenticated user can view routines. Only advanced staff
 * or the original creator may mutate a routine. Only admins may delete.
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class RoutinePolicy
{
    /**
     * Determines whether the user can list routines.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific routine.
     *
     * @param  User     $user
     * @param  Routine  $routine
     * @return bool
     */
    public function view(User $user, Routine $routine): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create a routine.
     * Only advanced staff may create platform routines.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can update a routine.
     * Advanced staff may update any routine; a user may only update their own created routines.
     *
     * @param  User     $user
     * @param  Routine  $routine
     * @return bool
     */
    public function update(User $user, Routine $routine): bool
    {
        if ($user->isAdvanced()) {
            return true;
        }

        return $user->id === $routine->creator_id;
    }

    /**
     * Determines whether the user can delete a routine.
     * Only admins may delete routines (Gate::before handles this).
     *
     * @param  User     $user
     * @param  Routine  $routine
     * @return bool
     */
    public function delete(User $user, Routine $routine): bool
    {
        return false;
    }
}

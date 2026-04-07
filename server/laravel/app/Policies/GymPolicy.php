<?php

namespace App\Policies;

use App\Models\Gym;
use App\Models\User;

/**
 * Authorization policy for Gym resources.
 *
 * SRP: Solely responsible for determining who can perform actions on gym records.
 * OCP: New gym abilities are added as methods without altering existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 * Managers may only view their own gym; creation and deletion are admin-only.
 */
class GymPolicy
{
    /**
     * Determines whether any user can list gyms.
     * Gyms are publicly viewable.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific gym.
     *
     * @param  User  $user
     * @param  Gym   $gym
     * @return bool
     */
    public function view(User $user, Gym $gym): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create a gym.
     * Only admins may create gyms (Gate::before handles this).
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determines whether the user can update a gym.
     * Managers may update their own gym; other advanced staff cannot.
     *
     * @param  User  $user
     * @param  Gym   $gym
     * @return bool
     */
    public function update(User $user, Gym $gym): bool
    {
        return $user->isManager() && $user->current_gym_id === $gym->id;
    }

    /**
     * Determines whether the user can delete a gym.
     * Only admins may delete gyms (Gate::before handles this).
     *
     * @param  User  $user
     * @param  Gym   $gym
     * @return bool
     */
    public function delete(User $user, Gym $gym): bool
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\GymClass;
use App\Models\User;

/**
 * Authorization policy for GymClass resources.
 *
 * SRP: Solely responsible for determining who can perform actions on gym class sessions.
 * OCP: New abilities are added as methods without modifying existing authorization logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Multi-tenant rule: Advanced staff (manager/staff) may only mutate classes
 * that belong to their own assigned gym (current_gym_id === gym_class.gym_id).
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class GymClassPolicy
{
    /**
     * Determines whether any user can list classes.
     * Classes are publicly readable; admin scope is controlled in the controller.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific class.
     *
     * @param  User      $user
     * @param  GymClass  $gymClass
     * @return bool
     */
    public function view(User $user, GymClass $gymClass): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create a class.
     * Only advanced staff may create classes.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can update a class.
     * Advanced staff may only update classes within their own gym.
     *
     * @param  User      $user
     * @param  GymClass  $gymClass
     * @return bool
     */
    public function update(User $user, GymClass $gymClass): bool
    {
        return $user->isAdvanced() && $user->current_gym_id === $gymClass->gym_id;
    }

    /**
     * Determines whether the user can cancel or delete a class.
     * Advanced staff may only delete classes within their own gym.
     *
     * @param  User      $user
     * @param  GymClass  $gymClass
     * @return bool
     */
    public function delete(User $user, GymClass $gymClass): bool
    {
        return $user->isAdvanced() && $user->current_gym_id === $gymClass->gym_id;
    }
}

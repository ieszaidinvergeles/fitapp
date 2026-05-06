<?php

namespace App\Policies;

use App\Models\User;

/**
 * Authorization policy for User resources.
 *
 * SRP: Solely responsible for determining access to user profile records.
 * OCP: New user abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Ownership rule: A standard user may only view or update their own profile.
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class UserPolicy
{
    /**
     * Determines whether the user can list all users.
     * Only advanced staff may list all platform users.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageMembers();
    }

    /**
     * Determines whether the user can view a specific user profile.
     * Advanced staff can view any profile; a user may only view their own.
     *
     * @param  User  $user
     * @param  User  $model
     * @return bool
     */
    public function view(User $user, User $model): bool
    {
        if ($user->canManageMembers()) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determines whether the user can create a new user record.
     * Only advanced staff may create users programmatically.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->canManageMembers();
    }

    /**
     * Determines whether the user can update a user profile.
     * Advanced staff may update any profile; a user may only update their own.
     *
     * @param  User  $user
     * @param  User  $model
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        if ($user->canManageMembers()) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determines whether the user can delete a user record.
     * Only admins may delete users (Gate::before handles this).
     *
     * @param  User  $user
     * @param  User  $model
     * @return bool
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }
}

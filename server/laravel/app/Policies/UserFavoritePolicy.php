<?php

namespace App\Policies;

use App\Models\UserFavorite;
use App\Models\User;

/**
 * Authorization policy for UserFavorite resources.
 *
 * SRP: Solely responsible for determining access to user favourite records.
 * OCP: New favourite abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Ownership rule: A user may only manage their own favourites.
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class UserFavoritePolicy
{
    /**
     * Determines whether the user can list favourites.
     * Users only ever see their own (scoped in controller).
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create a favourite.
     * Any authenticated user may save a favourite.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can delete a favourite entry.
     * A user may only remove their own favourites.
     *
     * @param  User          $user
     * @param  UserFavorite  $userFavorite
     * @return bool
     */
    public function delete(User $user, UserFavorite $userFavorite): bool
    {
        return $user->id === $userFavorite->user_id;
    }
}

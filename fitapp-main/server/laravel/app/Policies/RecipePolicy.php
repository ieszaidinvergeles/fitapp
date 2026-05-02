<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

/**
 * Authorization policy for Recipe resources.
 *
 * SRP: Solely responsible for determining access to recipe records.
 * OCP: New recipe abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Public read rule: Recipes are publicly viewable. Advanced staff may create and update.
 * Only admins may delete. Admin bypass is handled via Gate::before.
 */
class RecipePolicy
{
    /**
     * Determines whether the user can list recipes.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific recipe.
     *
     * @param  User    $user
     * @param  Recipe  $recipe
     * @return bool
     */
    public function view(User $user, Recipe $recipe): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create a recipe.
     * Only advanced staff may create recipes.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can update a recipe.
     * Only advanced staff may update recipes.
     *
     * @param  User    $user
     * @param  Recipe  $recipe
     * @return bool
     */
    public function update(User $user, Recipe $recipe): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can delete a recipe.
     * Only admins may delete recipes (Gate::before handles this).
     *
     * @param  User    $user
     * @param  Recipe  $recipe
     * @return bool
     */
    public function delete(User $user, Recipe $recipe): bool
    {
        return false;
    }
}

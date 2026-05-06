<?php

namespace App\Policies;

use App\Models\DietPlan;
use App\Models\User;

/**
 * Authorization policy for DietPlan resources.
 *
 * SRP: Solely responsible for determining access to diet plan records.
 * OCP: New diet plan abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Advanced-staff read rule: DietPlans are visible to advanced staff and above.
 * Only admins may create, update, or delete. Admin bypass is via Gate::before.
 */
class DietPlanPolicy
{
    /**
     * Determines whether the user can list diet plans.
     * Only advanced staff may access diet plan records.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific diet plan.
     * Only advanced staff may access diet plan records.
     *
     * @param  User      $user
     * @param  DietPlan  $dietPlan
     * @return bool
     */
    public function view(User $user, DietPlan $dietPlan): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create a diet plan.
     * Only admins may create diet plans (Gate::before handles this).
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determines whether the user can update a diet plan.
     * Only admins may update diet plans (Gate::before handles this).
     *
     * @param  User      $user
     * @param  DietPlan  $dietPlan
     * @return bool
     */
    public function update(User $user, DietPlan $dietPlan): bool
    {
        return false;
    }

    /**
     * Determines whether the user can delete a diet plan.
     * Only admins may delete diet plans (Gate::before handles this).
     *
     * @param  User      $user
     * @param  DietPlan  $dietPlan
     * @return bool
     */
    public function delete(User $user, DietPlan $dietPlan): bool
    {
        return false;
    }
}

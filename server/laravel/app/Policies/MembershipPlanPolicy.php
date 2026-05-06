<?php

namespace App\Policies;

use App\Models\MembershipPlan;
use App\Models\User;

/**
 * Authorization policy for MembershipPlan resources.
 *
 * SRP: Solely responsible for determining access to membership plan records.
 * OCP: New membership plan abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Public read rule: Membership plans are publicly viewable (for the landing page).
 * Only admins may create, update, or delete. Admin bypass is via Gate::before.
 */
class MembershipPlanPolicy
{
    /**
     * Determines whether any user can list membership plans.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific membership plan.
     *
     * @param  User            $user
     * @param  MembershipPlan  $membershipPlan
     * @return bool
     */
    public function view(User $user, MembershipPlan $membershipPlan): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create a membership plan.
     * Only admins may create plans (Gate::before handles this).
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determines whether the user can update a membership plan.
     * Only admins may update plans (Gate::before handles this).
     *
     * @param  User            $user
     * @param  MembershipPlan  $membershipPlan
     * @return bool
     */
    public function update(User $user, MembershipPlan $membershipPlan): bool
    {
        return false;
    }

    /**
     * Determines whether the user can delete a membership plan.
     * Only admins may delete plans (Gate::before handles this).
     *
     * @param  User            $user
     * @param  MembershipPlan  $membershipPlan
     * @return bool
     */
    public function delete(User $user, MembershipPlan $membershipPlan): bool
    {
        return false;
    }
}

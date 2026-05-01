<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

/**
 * Authorization policy for Activity resources.
 *
 * SRP: Solely responsible for determining access to activity records.
 * OCP: New activity abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Public read rule: Activities are publicly viewable. Only admins may mutate them.
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class ActivityPolicy
{
    /**
     * Determines whether the user can list activities.
     * Activities are publicly available.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific activity.
     *
     * @param  User      $user
     * @param  Activity  $activity
     * @return bool
     */
    public function view(User $user, Activity $activity): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create an activity.
     * Only admins may create activities (Gate::before handles this).
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determines whether the user can update an activity.
     * Only admins may update activities (Gate::before handles this).
     *
     * @param  User      $user
     * @param  Activity  $activity
     * @return bool
     */
    public function update(User $user, Activity $activity): bool
    {
        return false;
    }

    /**
     * Determines whether the user can delete an activity.
     * Only admins may delete activities (Gate::before handles this).
     *
     * @param  User      $user
     * @param  Activity  $activity
     * @return bool
     */
    public function delete(User $user, Activity $activity): bool
    {
        return false;
    }
}

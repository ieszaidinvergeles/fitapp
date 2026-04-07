<?php

namespace App\Policies;

use App\Models\BodyMetric;
use App\Models\User;

/**
 * Authorization policy for BodyMetric resources.
 *
 * SRP: Solely responsible for determining access to body metric records.
 * OCP: New metric abilities are added as methods without modifying existing authorization logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Ownership rule: A standard user may only access their own body metrics.
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class BodyMetricPolicy
{
    /**
     * Determines whether the user can list body metrics.
     * Advanced staff may access all records; users only their own (scoped in controller).
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific body metric record.
     * Advanced staff may view any; a standard user may only view their own.
     *
     * @param  User        $user
     * @param  BodyMetric  $bodyMetric
     * @return bool
     */
    public function view(User $user, BodyMetric $bodyMetric): bool
    {
        if ($user->isAdvanced()) {
            return true;
        }

        return $user->id === $bodyMetric->user_id;
    }

    /**
     * Determines whether the user can create a body metric entry.
     * Any authenticated user may log their own metrics.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can delete a body metric record.
     * Only admins may delete records (Gate::before handles this).
     *
     * @param  User        $user
     * @param  BodyMetric  $bodyMetric
     * @return bool
     */
    public function delete(User $user, BodyMetric $bodyMetric): bool
    {
        return false;
    }
}

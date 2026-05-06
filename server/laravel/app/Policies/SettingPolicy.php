<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

/**
 * Authorization policy for Setting resources.
 *
 * SRP: Solely responsible for determining access to user setting records.
 * OCP: New setting abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Ownership rule: A user may only view or update their own settings.
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class SettingPolicy
{
    /**
     * Determines whether the user can view their own settings record.
     * A user may only access their own configuration.
     *
     * @param  User     $user
     * @param  Setting  $setting
     * @return bool
     */
    public function view(User $user, Setting $setting): bool
    {
        return $user->id === $setting->user_id;
    }

    /**
     * Determines whether the user can update their own settings.
     *
     * @param  User     $user
     * @param  Setting  $setting
     * @return bool
     */
    public function update(User $user, Setting $setting): bool
    {
        return $user->id === $setting->user_id;
    }
}

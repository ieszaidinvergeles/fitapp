<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;

/**
 * Authorization policy for Equipment resources.
 *
 * SRP: Solely responsible for determining access to equipment records.
 * OCP: New equipment abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Public read rule: Equipment is publicly viewable. Only admins may mutate it.
 * Admin bypass is handled globally via Gate::before in AppServiceProvider.
 */
class EquipmentPolicy
{
    /**
     * Determines whether the user can list equipment.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determines whether the user can view a specific equipment record.
     *
     * @param  User       $user
     * @param  Equipment  $equipment
     * @return bool
     */
    public function view(User $user, Equipment $equipment): bool
    {
        return true;
    }

    /**
     * Determines whether the user can create an equipment record.
     * Only admins may create equipment (Gate::before handles this).
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determines whether the user can update an equipment record.
     * Only admins may update equipment (Gate::before handles this).
     *
     * @param  User       $user
     * @param  Equipment  $equipment
     * @return bool
     */
    public function update(User $user, Equipment $equipment): bool
    {
        return false;
    }

    /**
     * Determines whether the user can delete an equipment record.
     * Only admins may delete equipment (Gate::before handles this).
     *
     * @param  User       $user
     * @param  Equipment  $equipment
     * @return bool
     */
    public function delete(User $user, Equipment $equipment): bool
    {
        return false;
    }
}

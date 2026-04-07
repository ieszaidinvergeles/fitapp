<?php

namespace App\Policies;

use App\Models\StaffAttendance;
use App\Models\User;

/**
 * Authorization policy for StaffAttendance resources.
 *
 * SRP: Solely responsible for determining access to staff clock-in/clock-out records.
 * OCP: New attendance abilities are added as methods without modifying existing logic.
 * LSP: Substitutable for any policy implementation contracted by the Gate.
 *
 * Multi-tenant + Ownership rule: A staff member may only view or clock-out their own records.
 * Managers may view attendance from their own gym. Admin bypass is via Gate::before.
 */
class StaffAttendancePolicy
{
    /**
     * Determines whether the user can list staff attendance records.
     * Only advanced staff (manager/staff/admin) may list attendance records.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can view a specific attendance record.
     * A staff member may only view their own record; a manager may view records from their gym.
     *
     * @param  User            $user
     * @param  StaffAttendance $attendance
     * @return bool
     */
    public function view(User $user, StaffAttendance $attendance): bool
    {
        if ($user->isManager()) {
            return $user->current_gym_id === $attendance->gym_id;
        }

        return $user->id === $attendance->staff_id;
    }

    /**
     * Determines whether the user can create (clock-in) a new attendance record.
     * Only advanced staff may clock in.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isAdvanced();
    }

    /**
     * Determines whether the user can update (clock-out) an attendance record.
     * A staff member may only update their own open record.
     *
     * @param  User            $user
     * @param  StaffAttendance $attendance
     * @return bool
     */
    public function update(User $user, StaffAttendance $attendance): bool
    {
        return $user->id === $attendance->staff_id;
    }

    /**
     * Determines whether the user can delete a staff attendance record.
     * Only admins may delete records (Gate::before handles this).
     *
     * @param  User            $user
     * @param  StaffAttendance $attendance
     * @return bool
     */
    public function delete(User $user, StaffAttendance $attendance): bool
    {
        return false;
    }
}

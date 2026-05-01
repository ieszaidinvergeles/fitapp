<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\StaffAttendance;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the staff_attendance table.
 *
 * SRP: Solely responsible for populating staff attendance records.
 * NOTE: Creates a 30-day history for each staff member at their gym.
 */
class StaffAttendanceSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $staff = User::where('role', 'staff')->get();
        $gyms  = Gym::all();

        if ($staff->isEmpty() || $gyms->isEmpty()) {
            return;
        }

        $staff->each(function (User $member) use ($gyms): void {
            $gym = $gyms->random();

            for ($day = 30; $day >= 1; $day--) {
                if (rand(1, 7) === 1) {
                    continue;
                }

                $clockIn  = now()->subDays($day)->setHour(rand(7, 9))->setMinute(0);
                $clockOut = (clone $clockIn)->modify('+8 hours');

                StaffAttendance::create([
                    'staff_id'  => $member->id,
                    'gym_id'    => $gym->id,
                    'clock_in'  => $clockIn,
                    'clock_out' => $clockOut,
                    'date'      => $clockIn->format('Y-m-d'),
                ]);
            }
        });
    }
}

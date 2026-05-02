<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\StaffAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for StaffAttendance.
 *
 * SRP: Solely responsible for generating fake StaffAttendance data.
 */
class StaffAttendanceFactory extends Factory
{
    /** @var class-string<StaffAttendance> */
    protected $model = StaffAttendance::class;

    /** @inheritdoc */
    public function definition(): array
    {
        $clockIn  = fake()->dateTimeBetween('-30 days', 'now');
        $clockOut = (clone $clockIn)->modify('+8 hours');

        return [
            'staff_id'  => User::where('role', 'staff')->inRandomOrder()->first()?->id,
            'gym_id'    => Gym::inRandomOrder()->first()?->id,
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
            'date'      => $clockIn->format('Y-m-d'),
        ];
    }
}

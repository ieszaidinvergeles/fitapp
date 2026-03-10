<?php

namespace Database\Seeders;

use App\Models\StaffAttendance;
use Illuminate\Database\Seeder;

/**
 * Seeds the staff_attendance table.
 *
 * SRP: Solely responsible for populating staff attendance records.
 */
class StaffAttendanceSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        StaffAttendance::factory()->count(50)->create();
    }
}

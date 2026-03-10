<?php

namespace Database\Seeders;

use App\Models\UserMealSchedule;
use Illuminate\Database\Seeder;

/**
 * Seeds the user_meal_schedule table.
 *
 * SRP: Solely responsible for populating meal schedule records.
 */
class UserMealScheduleSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        UserMealSchedule::factory()->count(60)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\DietPlan;
use Illuminate\Database\Seeder;

/**
 * Seeds the diet_plans table.
 *
 * SRP: Solely responsible for populating diet plan records.
 */
class DietPlanSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        DietPlan::factory()->count(6)->create();
    }
}

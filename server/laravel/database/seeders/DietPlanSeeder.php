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
        $fixed = [
            ['name' => 'Definition',    'goal_description' => 'Reduce body fat while preserving muscle mass.'],
            ['name' => 'Bulk',          'goal_description' => 'Caloric surplus to maximise muscle gain.'],
            ['name' => 'Keto',          'goal_description' => 'High fat, very low carb metabolic state.'],
            ['name' => 'Paleo',         'goal_description' => 'Whole foods based on ancestral eating patterns.'],
            ['name' => 'Mediterranean', 'goal_description' => 'Balanced plan rich in healthy fats and vegetables.'],
            ['name' => 'Vegan',         'goal_description' => 'Plant-based nutrition for performance and health.'],
            ['name' => 'Maintenance',   'goal_description' => 'Caloric balance to sustain current body weight.'],
            ['name' => 'Recomp',        'goal_description' => 'Simultaneous fat loss and muscle gain.'],
        ];

        foreach ($fixed as $data) {
            DietPlan::create($data);
        }

    }
}

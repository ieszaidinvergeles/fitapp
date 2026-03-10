<?php

namespace Database\Seeders;

use App\Models\DietPlan;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the routines table.
 *
 * SRP: Solely responsible for populating routine records.
 * NOTE: Creates fixed records covering all difficulty_level ENUM values
 *       (beginner, intermediate, advanced, expert).
 */
class RoutineSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $creator  = User::where('role', 'admin')->first();
        $dietPlan = DietPlan::first();

        $fixed = [
            ['name' => 'Full Body Starter',      'difficulty_level' => 'beginner',     'estimated_duration_min' => 30],
            ['name' => 'Cardio Foundation',       'difficulty_level' => 'beginner',     'estimated_duration_min' => 25],
            ['name' => 'Upper Lower Split',       'difficulty_level' => 'intermediate', 'estimated_duration_min' => 45],
            ['name' => 'Push Pull Legs',          'difficulty_level' => 'intermediate', 'estimated_duration_min' => 60],
            ['name' => 'Hypertrophy Block',       'difficulty_level' => 'intermediate', 'estimated_duration_min' => 60],
            ['name' => 'Strength Powerlifting',   'difficulty_level' => 'advanced',     'estimated_duration_min' => 75],
            ['name' => 'Olympic Weightlifting',   'difficulty_level' => 'advanced',     'estimated_duration_min' => 90],
            ['name' => 'CrossFit Competition',    'difficulty_level' => 'expert',       'estimated_duration_min' => 90],
            ['name' => 'Elite Athletic Program',  'difficulty_level' => 'expert',       'estimated_duration_min' => 120],
        ];

        foreach ($fixed as $data) {
            Routine::create(array_merge($data, [
                'description'            => 'Structured training program for ' . $data['difficulty_level'] . ' athletes.',
                'creator_id'             => $creator?->id,
                'associated_diet_plan_id'=> $dietPlan?->id,
            ]));
        }

        Routine::factory()->count(11)->create();
    }
}

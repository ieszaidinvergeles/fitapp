<?php

namespace Database\Factories;

use App\Models\DietPlan;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Routine.
 *
 * SRP: Solely responsible for generating fake Routine data.
 */
class RoutineFactory extends Factory
{
    /** @var class-string<Routine> */
    protected $model = Routine::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'name'                   => fake()->words(3, true) . ' Routine',
            'description'            => fake()->paragraph(),
            'creator_id'             => User::inRandomOrder()->first()?->id,
            'difficulty_level'       => fake()->randomElement(['beginner', 'intermediate', 'advanced', 'expert']),
            'estimated_duration_min' => fake()->randomElement([30, 45, 60, 90]),
            'associated_diet_plan_id' => DietPlan::inRandomOrder()->first()?->id,
        ];
    }
}

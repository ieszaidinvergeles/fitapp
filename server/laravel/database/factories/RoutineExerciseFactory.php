<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Routine;
use App\Models\RoutineExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for RoutineExercise.
 *
 * SRP: Solely responsible for generating fake RoutineExercise data.
 */
class RoutineExerciseFactory extends Factory
{
    /** @var class-string<RoutineExercise> */
    protected $model = RoutineExercise::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'routine_id'       => Routine::inRandomOrder()->first()?->id,
            'exercise_id'      => Exercise::inRandomOrder()->first()?->id,
            'order_index'      => fake()->numberBetween(1, 10),
            'recommended_sets' => fake()->numberBetween(2, 5),
            'recommended_reps' => fake()->numberBetween(6, 20),
            'rest_seconds'     => fake()->randomElement([30, 60, 90, 120]),
        ];
    }
}

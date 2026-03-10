<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Exercise.
 *
 * SRP: Solely responsible for generating fake Exercise data.
 */
class ExerciseFactory extends Factory
{
    /** @var class-string<Exercise> */
    protected $model = Exercise::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'name'                => fake()->randomElement([
                'Squat', 'Deadlift', 'Bench Press', 'Pull-up',
                'Push-up', 'Lunge', 'Plank', 'Bicep Curl',
                'Shoulder Press', 'Hip Thrust',
            ]),
            'description'         => fake()->paragraph(),
            'image_url'           => fake()->imageUrl(640, 480, 'sports'),
            'video_url'           => 'https://example.com/videos/' . fake()->slug(),
            'target_muscle_group' => fake()->randomElement([
                'Chest', 'Back', 'Legs', 'Shoulders', 'Arms', 'Core', 'Glutes',
            ]),
        ];
    }
}

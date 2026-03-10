<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Activity.
 *
 * SRP: Solely responsible for generating fake Activity data.
 */
class ActivityFactory extends Factory
{
    /** @var class-string<Activity> */
    protected $model = Activity::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'name'            => fake()->randomElement([
                'Yoga', 'CrossFit', 'Spinning', 'Pilates', 'Zumba',
                'Boxing', 'Swimming', 'HIIT', 'Bodybuilding', 'Stretching',
            ]),
            'description'     => fake()->sentence(),
            'intensity_level' => fake()->randomElement(['low', 'medium', 'high', 'extreme']),
        ];
    }
}

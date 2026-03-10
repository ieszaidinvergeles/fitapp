<?php

namespace Database\Factories;

use App\Models\BodyMetric;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for BodyMetric.
 *
 * SRP: Solely responsible for generating fake BodyMetric data.
 */
class BodyMetricFactory extends Factory
{
    /** @var class-string<BodyMetric> */
    protected $model = BodyMetric::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'user_id'         => User::inRandomOrder()->first()?->id,
            'date'            => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'weight_kg'       => fake()->numberBetween(50, 120),
            'height_cm'       => fake()->numberBetween(155, 200),
            'body_fat_pct'    => fake()->numberBetween(8, 35),
            'muscle_mass_pct' => fake()->numberBetween(30, 55),
        ];
    }
}

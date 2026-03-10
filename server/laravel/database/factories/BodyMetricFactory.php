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
            'weight_kg'       => fake()->randomFloat(1, 40, 180),
            'height_cm'       => fake()->randomFloat(1, 140, 220),
            'body_fat_pct'    => fake()->randomFloat(2, 5, 50),
            'muscle_mass_pct' => fake()->randomFloat(2, 20, 65),
        ];
    }
}

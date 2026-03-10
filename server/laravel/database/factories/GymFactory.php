<?php

namespace Database\Factories;

use App\Models\Gym;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Gym.
 *
 * SRP: Solely responsible for generating fake Gym data.
 * NOTE: manager_id is left null; assign after users are seeded.
 */
class GymFactory extends Factory
{
    /** @var class-string<Gym> */
    protected $model = Gym::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'name'            => fake()->company() . ' Fitness',
            'manager_id'      => null,
            'address'         => fake()->streetAddress(),
            'city'            => fake()->city(),
            'location_coords' => fake()->latitude() . ',' . fake()->longitude(),
            'phone'           => fake()->phoneNumber(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Gym;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Gym.
 *
 * SRP: Solely responsible for generating fake Gym data.
 * NOTE: manager_id is left null; assigned after users are seeded
 *       in DatabaseSeeder to resolve the circular FK dependency.
 */
class GymFactory extends Factory
{
    /** @var class-string<Gym> */
    protected $model = Gym::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'name'            => substr(fake()->company() . ' Fitness', 0, 80),
            'manager_id'      => null,
            'address'         => substr(fake()->streetAddress(), 0, 200),
            'city'            => fake()->city(),
            'location_coords' => fake()->latitude() . ',' . fake()->longitude(),
            'phone'           => substr(fake()->phoneNumber(), 0, 20),
        ];
    }
}

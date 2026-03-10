<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Gym;
use App\Models\GymInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for GymInventory.
 *
 * SRP: Solely responsible for generating fake GymInventory data.
 */
class GymInventoryFactory extends Factory
{
    /** @var class-string<GymInventory> */
    protected $model = GymInventory::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'gym_id'       => Gym::inRandomOrder()->first()?->id,
            'equipment_id' => Equipment::inRandomOrder()->first()?->id,
            'quantity'     => fake()->numberBetween(1, 20),
            'status'       => fake()->randomElement(['operational', 'maintenance']),
        ];
    }
}

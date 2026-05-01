<?php

namespace Database\Factories;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Equipment.
 *
 * SRP: Solely responsible for generating fake Equipment data.
 */
class EquipmentFactory extends Factory
{
    /** @var class-string<Equipment> */
    protected $model = Equipment::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'name'               => fake()->randomElement([
                'Barbell', 'Dumbbell', 'Kettlebell', 'Resistance Band',
                'Pull-up Bar', 'Treadmill', 'Rowing Machine', 'Yoga Mat',
            ]),
            'description'        => fake()->sentence(),
            'is_home_accessible' => fake()->boolean(40),
        ];
    }
}

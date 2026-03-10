<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Room.
 *
 * SRP: Solely responsible for generating fake Room data.
 */
class RoomFactory extends Factory
{
    /** @var class-string<Room> */
    protected $model = Room::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'gym_id'   => Gym::inRandomOrder()->first()?->id,
            'name'     => fake()->randomElement(['Room A', 'Room B', 'Spinning Hall', 'Yoga Studio', 'Main Floor']),
            'capacity' => fake()->numberBetween(10, 60),
        ];
    }
}

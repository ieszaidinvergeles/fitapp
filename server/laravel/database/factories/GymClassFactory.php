<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Gym;
use App\Models\GymClass;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for GymClass.
 *
 * SRP: Solely responsible for generating fake GymClass data.
 */
class GymClassFactory extends Factory
{
    /** @var class-string<GymClass> */
    protected $model = GymClass::class;

    /** @inheritdoc */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+30 days');
        $end   = (clone $start)->modify('+1 hour');

        return [
            'gym_id'         => Gym::inRandomOrder()->first()?->id,
            'activity_id'    => Activity::inRandomOrder()->first()?->id,
            'instructor_id'  => User::where('role', 'staff')->inRandomOrder()->first()?->id,
            'room_id'        => Room::inRandomOrder()->first()?->id,
            'start_time'     => $start,
            'end_time'       => $end,
            'capacity_limit' => fake()->numberBetween(10, 30),
            'is_cancelled'   => false,
        ];
    }
}

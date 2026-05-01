<?php

namespace Database\Factories;

use App\Models\Routine;
use App\Models\User;
use App\Models\UserActiveRoutine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for UserActiveRoutine.
 *
 * SRP: Solely responsible for generating fake UserActiveRoutine data.
 */
class UserActiveRoutineFactory extends Factory
{
    /** @var class-string<UserActiveRoutine> */
    protected $model = UserActiveRoutine::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'user_id'    => User::inRandomOrder()->first()?->id,
            'routine_id' => Routine::inRandomOrder()->first()?->id,
            'is_active'  => fake()->boolean(70),
            'start_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
        ];
    }
}

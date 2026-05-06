<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use App\Models\UserMealSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for UserMealSchedule.
 *
 * SRP: Solely responsible for generating fake UserMealSchedule data.
 */
class UserMealScheduleFactory extends Factory
{
    /** @var class-string<UserMealSchedule> */
    protected $model = UserMealSchedule::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'user_id'     => User::inRandomOrder()->first()?->id,
            'date'        => fake()->dateTimeBetween('now', '+7 days')->format('Y-m-d'),
            'meal_type'   => fake()->randomElement([
                'breakfast', 'lunch', 'dinner', 'snack', 'pre_workout', 'post_workout',
            ]),
            'recipe_id'   => Recipe::inRandomOrder()->first()?->id,
            'is_consumed' => fake()->boolean(40),
        ];
    }
}

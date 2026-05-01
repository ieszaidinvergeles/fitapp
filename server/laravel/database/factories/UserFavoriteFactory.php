<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for UserFavorite.
 *
 * SRP: Solely responsible for generating fake UserFavorite data.
 */
class UserFavoriteFactory extends Factory
{
    /** @var class-string<UserFavorite> */
    protected $model = UserFavorite::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'user_id'     => User::inRandomOrder()->first()?->id,
            'entity_type' => fake()->randomElement(['gym', 'activity', 'routine']),
            'entity_id'   => fake()->numberBetween(1, 10),
        ];
    }
}

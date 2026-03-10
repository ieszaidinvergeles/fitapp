<?php

namespace Database\Factories;

use App\Models\AppNotification;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for AppNotification.
 *
 * SRP: Solely responsible for generating fake AppNotification data.
 */
class AppNotificationFactory extends Factory
{
    /** @var class-string<AppNotification> */
    protected $model = AppNotification::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'sender_id'       => User::whereIn('role', ['admin', 'manager'])->inRandomOrder()->first()?->id,
            'title'           => fake()->sentence(4),
            'body'            => fake()->paragraph(),
            'target_audience' => fake()->randomElement(['global', 'staff_only', 'specific_gym', 'specific_user']),
            'related_gym_id'  => Gym::inRandomOrder()->first()?->id,
            'created_at'      => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}

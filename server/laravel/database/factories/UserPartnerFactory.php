<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPartner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for UserPartner.
 *
 * SRP: Solely responsible for generating fake UserPartner data.
 */
class UserPartnerFactory extends Factory
{
    /** @var class-string<UserPartner> */
    protected $model = UserPartner::class;

    /** @inheritdoc */
    public function definition(): array
    {
        $users = User::inRandomOrder()->limit(2)->get();

        return [
            'primary_user_id' => $users->first()?->id ?? 1,
            'partner_user_id' => $users->last()?->id ?? 2,
            'linked_at'       => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}

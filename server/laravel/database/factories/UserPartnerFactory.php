<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPartner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for UserPartner.
 *
 * SRP: Solely responsible for generating fake UserPartner data.
 * NOTE: Fetches two distinct users to prevent linking a user to themselves.
 */
class UserPartnerFactory extends Factory
{
    /** @var class-string<UserPartner> */
    protected $model = UserPartner::class;

    /** @inheritdoc */
    public function definition(): array
    {
        $primary = User::inRandomOrder()->first();
        $partner = User::where('id', '!=', $primary->id)->inRandomOrder()->first();

        return [
            'primary_user_id' => $primary->id,
            'partner_user_id' => $partner->id,
            'linked_at'       => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}

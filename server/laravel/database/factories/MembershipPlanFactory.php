<?php

namespace Database\Factories;

use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for MembershipPlan.
 *
 * SRP: Solely responsible for generating fake MembershipPlan data.
 */
class MembershipPlanFactory extends Factory
{
    /** @var class-string<MembershipPlan> */
    protected $model = MembershipPlan::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'name'               => fake()->randomElement(['Basic', 'Pro', 'Elite', 'Online Plus', 'Duo Premium']),
            'type'               => fake()->randomElement(['physical', 'online', 'duo']),
            'allow_partner_link' => fake()->boolean(30),
            'price'              => fake()->randomElement([19, 29, 49, 59, 79, 99]),
        ];
    }
}

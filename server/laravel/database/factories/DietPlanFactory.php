<?php

namespace Database\Factories;

use App\Models\DietPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for DietPlan.
 *
 * SRP: Solely responsible for generating fake DietPlan data.
 */
class DietPlanFactory extends Factory
{
    /** @var class-string<DietPlan> */
    protected $model = DietPlan::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'name'             => fake()->randomElement(['Definition', 'Bulk', 'Keto', 'Paleo', 'Mediterranean', 'Vegan']),
            'goal_description' => fake()->paragraph(),
        ];
    }
}

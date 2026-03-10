<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Recipe.
 *
 * SRP: Solely responsible for generating fake Recipe data.
 */
class RecipeFactory extends Factory
{
    /** @var class-string<Recipe> */
    protected $model = Recipe::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'name'              => fake()->words(3, true),
            'description'       => fake()->sentence(),
            'ingredients'       => implode(', ', fake()->words(6)),
            'preparation_steps' => fake()->paragraphs(2, true),
            'calories'          => fake()->numberBetween(100, 800),
            'macros_json'       => [
                'protein' => fake()->numberBetween(5, 60),
                'carbs'   => fake()->numberBetween(5, 100),
                'fat'     => fake()->numberBetween(2, 40),
            ],
            'type'              => fake()->randomElement([
                'breakfast', 'lunch', 'dinner', 'snack', 'pre_workout', 'post_workout',
            ]),
            'image_url'         => fake()->imageUrl(640, 480, 'food'),
        ];
    }
}

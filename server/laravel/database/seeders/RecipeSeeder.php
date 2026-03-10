<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;

/**
 * Seeds the recipes table.
 *
 * SRP: Solely responsible for populating recipe records.
 * NOTE: Creates fixed records covering all type ENUM values
 *       (breakfast, lunch, dinner, snack, pre_workout, post_workout).
 */
class RecipeSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $fixed = [
            ['type' => 'breakfast',   'name' => 'Oats with Banana',         'calories' => 350, 'macros_json' => ['protein' => 12, 'carbs' => 60, 'fat' => 6]],
            ['type' => 'breakfast',   'name' => 'Egg White Omelette',        'calories' => 220, 'macros_json' => ['protein' => 30, 'carbs' => 5,  'fat' => 8]],
            ['type' => 'breakfast',   'name' => 'Greek Yogurt Bowl',         'calories' => 280, 'macros_json' => ['protein' => 20, 'carbs' => 35, 'fat' => 5]],
            ['type' => 'lunch',       'name' => 'Chicken and Rice',          'calories' => 550, 'macros_json' => ['protein' => 45, 'carbs' => 60, 'fat' => 10]],
            ['type' => 'lunch',       'name' => 'Tuna Salad',                'calories' => 400, 'macros_json' => ['protein' => 40, 'carbs' => 20, 'fat' => 15]],
            ['type' => 'lunch',       'name' => 'Quinoa Buddha Bowl',        'calories' => 480, 'macros_json' => ['protein' => 18, 'carbs' => 70, 'fat' => 14]],
            ['type' => 'dinner',      'name' => 'Salmon with Vegetables',    'calories' => 500, 'macros_json' => ['protein' => 40, 'carbs' => 25, 'fat' => 22]],
            ['type' => 'dinner',      'name' => 'Turkey Stir Fry',           'calories' => 460, 'macros_json' => ['protein' => 38, 'carbs' => 40, 'fat' => 12]],
            ['type' => 'dinner',      'name' => 'Lentil Soup',               'calories' => 380, 'macros_json' => ['protein' => 22, 'carbs' => 55, 'fat' => 6]],
            ['type' => 'snack',       'name' => 'Protein Bar',               'calories' => 160, 'macros_json' => ['protein' => 20, 'carbs' => 22, 'fat' => 7]],
            ['type' => 'snack',       'name' => 'Apple with Peanut Butter',  'calories' => 250, 'macros_json' => ['protein' => 6,  'carbs' => 30, 'fat' => 12]],
            ['type' => 'snack',       'name' => 'Mixed Nuts',                'calories' => 180, 'macros_json' => ['protein' => 5,  'carbs' => 8,  'fat' => 16]],
            ['type' => 'pre_workout', 'name' => 'Banana and Coffee',         'calories' => 130, 'macros_json' => ['protein' => 2,  'carbs' => 28, 'fat' => 1]],
            ['type' => 'pre_workout', 'name' => 'Rice Cakes with Jam',       'calories' => 160, 'macros_json' => ['protein' => 3,  'carbs' => 35, 'fat' => 1]],
            ['type' => 'pre_workout', 'name' => 'Oat and Honey Shake',       'calories' => 310, 'macros_json' => ['protein' => 10, 'carbs' => 58, 'fat' => 4]],
            ['type' => 'post_workout','name' => 'Whey Protein Shake',        'calories' => 180, 'macros_json' => ['protein' => 30, 'carbs' => 10, 'fat' => 3]],
            ['type' => 'post_workout','name' => 'Chicken with Sweet Potato', 'calories' => 520, 'macros_json' => ['protein' => 42, 'carbs' => 55, 'fat' => 8]],
            ['type' => 'post_workout','name' => 'Cottage Cheese with Berries','calories' => 160,'macros_json' => ['protein' => 24, 'carbs' => 18, 'fat' => 4]],
        ];

        foreach ($fixed as $data) {
            Recipe::create(array_merge($data, [
                'description'       => 'Nutritious recipe for your training goals.',
                'ingredients'       => 'See preparation steps for full ingredient list.',
                'preparation_steps' => 'Mix all ingredients and serve.',
                'image_url'         => null,
            ]));
        }

        Recipe::factory()->count(12)->create();
    }
}

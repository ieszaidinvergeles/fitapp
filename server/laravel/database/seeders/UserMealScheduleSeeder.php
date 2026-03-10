<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\User;
use App\Models\UserMealSchedule;
use Illuminate\Database\Seeder;

/**
 * Seeds the user_meal_schedule table.
 *
 * SRP: Solely responsible for populating meal schedule records.
 * NOTE: Creates a full day meal plan for each client covering all
 *       meal_type ENUM values, then fills with factory data.
 */
class UserMealScheduleSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack', 'pre_workout', 'post_workout'];
        $recipes   = Recipe::all();

        if ($recipes->isEmpty()) {
            return;
        }

        User::where('role', 'client')->limit(20)->get()
            ->each(function (User $user) use ($mealTypes, $recipes): void {
                foreach ($mealTypes as $mealType) {
                    $recipe = $recipes->where('type', $mealType)->first()
                        ?? $recipes->random();

                    UserMealSchedule::create([
                        'user_id'     => $user->id,
                        'date'        => now()->addDays(rand(0, 6))->format('Y-m-d'),
                        'meal_type'   => $mealType,
                        'recipe_id'   => $recipe->id,
                        'is_consumed' => false,
                    ]);
                }
            });

        UserMealSchedule::factory()->count(60)->create();
    }
}

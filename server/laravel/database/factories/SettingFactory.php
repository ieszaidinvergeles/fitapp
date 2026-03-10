<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Setting.
 *
 * SRP: Solely responsible for generating fake Setting data.
 */
class SettingFactory extends Factory
{
    /** @var class-string<Setting> */
    protected $model = Setting::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'user_id'             => User::inRandomOrder()->first()?->id,
            'share_workout_stats' => fake()->boolean(70),
            'share_body_metrics'  => fake()->boolean(30),
            'share_attendance'    => fake()->boolean(60),
            'theme_preference'    => fake()->randomElement(['light', 'dark']),
            'language_preference' => fake()->randomElement(['en', 'es', 'fr', 'de']),
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the settings table.
 *
 * SRP: Solely responsible for populating user setting records.
 * NOTE: Creates exactly one setting per user to respect the PK constraint.
 */
class SettingSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        User::all()->each(function (User $user): void {
            Setting::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'share_workout_stats' => true,
                    'share_body_metrics'  => false,
                    'share_attendance'    => true,
                    'theme_preference'    => 'dark',
                    'language_preference' => 'en',
                ]
            );
        });
    }
}

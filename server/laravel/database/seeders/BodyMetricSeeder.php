<?php

namespace Database\Seeders;

use App\Models\BodyMetric;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the body_metrics table.
 *
 * SRP: Solely responsible for populating body metric records.
 * NOTE: Creates a 6-month progression series for a sample of users
 *       so the data looks realistic over time, then fills with factory data.
 */
class BodyMetricSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        User::where('role', 'client')->limit(10)->get()
            ->each(function (User $user): void {
                for ($month = 6; $month >= 0; $month--) {
                    BodyMetric::create([
                        'user_id'         => $user->id,
                        'date'            => now()->subMonths($month)->format('Y-m-d'),
                        'weight_kg'       => round(70 + rand(-10, 10) + ($month * 0.3), 1),
                        'height_cm'       => 175.0,
                        'body_fat_pct'    => round(20 + rand(-5, 5) - ($month * 0.2), 2),
                        'muscle_mass_pct' => round(40 + rand(-5, 5) + ($month * 0.1), 2),
                    ]);
                }
            });

    }
}

<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the notifications table.
 *
 * SRP: Solely responsible for populating notification records.
 * NOTE: Creates one record per target_audience ENUM value to guarantee
 *       all combinations are represented, then fills with factory data.
 */
class AppNotificationSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $gym   = Gym::first();

        $fixed = [
            [
                'target_audience' => 'global',
                'title'           => 'Welcome to FitZone!',
                'body'            => 'New features are now available for all members.',
                'related_gym_id'  => null,
            ],
            [
                'target_audience' => 'staff_only',
                'title'           => 'Staff Meeting This Friday',
                'body'            => 'Mandatory staff briefing at 9:00 AM in the main hall.',
                'related_gym_id'  => $gym?->id,
            ],
            [
                'target_audience' => 'specific_gym',
                'title'           => 'Maintenance Notice',
                'body'            => 'The pool will be closed for maintenance this weekend.',
                'related_gym_id'  => $gym?->id,
            ],
            [
                'target_audience' => 'specific_user',
                'title'           => 'Your booking was confirmed',
                'body'            => 'Your class booking for tomorrow has been confirmed.',
                'related_gym_id'  => null,
            ],
        ];

        foreach ($fixed as $data) {
            AppNotification::create(array_merge($data, [
                'sender_id'  => $admin?->id,
                'created_at' => now()->subDays(rand(1, 10)),
            ]));
        }

        AppNotification::factory()->count(20)->create();
    }
}

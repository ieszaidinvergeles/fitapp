<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the notifications table.
 *
 * SRP: Solely responsible for populating notification records.
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
                'sender_id'       => $admin?->id,
            ],
            [
                'target_audience' => 'staff_only',
                'title'           => 'Staff Meeting This Friday',
                'body'            => 'Mandatory staff briefing at 9:00 AM in the main hall.',
                'related_gym_id'  => $gym?->id,
                'sender_id'       => $admin?->id,
            ],
            [
                'target_audience' => 'specific_gym',
                'title'           => 'Maintenance Notice',
                'body'            => 'The pool will be closed for maintenance this weekend.',
                'related_gym_id'  => $gym?->id,
                'sender_id'       => $admin?->id,
            ]
        ];

        foreach ($fixed as $data) {
            Notification::create($data);
        }
    }
}

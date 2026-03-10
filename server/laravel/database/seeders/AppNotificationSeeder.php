<?php

namespace Database\Seeders;

use App\Models\AppNotification;
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
        AppNotification::factory()->count(20)->create();
    }
}

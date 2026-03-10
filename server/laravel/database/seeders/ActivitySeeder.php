<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

/**
 * Seeds the activities table.
 *
 * SRP: Solely responsible for populating activity records.
 */
class ActivitySeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        Activity::factory()->count(10)->create();
    }
}

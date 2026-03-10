<?php

namespace Database\Seeders;

use App\Models\BodyMetric;
use Illuminate\Database\Seeder;

/**
 * Seeds the body_metrics table.
 *
 * SRP: Solely responsible for populating body metric records.
 */
class BodyMetricSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        BodyMetric::factory()->count(100)->create();
    }
}

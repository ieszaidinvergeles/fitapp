<?php

namespace Database\Seeders;

use App\Models\Routine;
use Illuminate\Database\Seeder;

/**
 * Seeds the routines table.
 *
 * SRP: Solely responsible for populating routine records.
 */
class RoutineSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        Routine::factory()->count(20)->create();
    }
}

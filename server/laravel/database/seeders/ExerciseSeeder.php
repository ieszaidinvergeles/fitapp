<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

/**
 * Seeds the exercises table.
 *
 * SRP: Solely responsible for populating exercise records.
 */
class ExerciseSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        Exercise::factory()->count(20)->create();
    }
}

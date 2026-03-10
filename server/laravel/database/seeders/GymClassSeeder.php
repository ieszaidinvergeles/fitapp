<?php

namespace Database\Seeders;

use App\Models\GymClass;
use Illuminate\Database\Seeder;

/**
 * Seeds the classes table.
 *
 * SRP: Solely responsible for populating gym class records.
 */
class GymClassSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        GymClass::factory()->count(30)->create();
    }
}

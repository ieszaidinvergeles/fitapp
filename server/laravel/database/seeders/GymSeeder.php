<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Seeder;

/**
 * Seeds the gyms table.
 *
 * SRP: Solely responsible for populating gym records.
 * NOTE: manager_id is assigned by DatabaseSeeder after users are created
 *       to resolve the circular dependency between gyms and users.
 */
class GymSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        Gym::factory()->count(5)->create();
    }
}

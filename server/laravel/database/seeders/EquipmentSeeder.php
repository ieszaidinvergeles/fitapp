<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

/**
 * Seeds the equipment table.
 *
 * SRP: Solely responsible for populating equipment records.
 */
class EquipmentSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        Equipment::factory()->count(15)->create();
    }
}

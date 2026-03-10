<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Gym;
use App\Models\GymInventory;
use Illuminate\Database\Seeder;

/**
 * Seeds the gym_inventory table.
 *
 * SRP: Solely responsible for populating gym inventory records.
 * NOTE: Iterates gyms and assigns a random subset of equipment
 *       to avoid duplicate composite-key violations.
 */
class GymInventorySeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $equipmentIds = Equipment::pluck('id')->toArray();

        Gym::all()->each(function (Gym $gym) use ($equipmentIds): void {
            $selected = array_slice(
                $equipmentIds,
                0,
                min(count($equipmentIds), 8)
            );

            shuffle($selected);

            foreach (array_unique($selected) as $equipmentId) {
                GymInventory::firstOrCreate(
                    ['gym_id' => $gym->id, 'equipment_id' => $equipmentId],
                    ['quantity' => rand(1, 15), 'status' => 'operational']
                );
            }
        });
    }
}

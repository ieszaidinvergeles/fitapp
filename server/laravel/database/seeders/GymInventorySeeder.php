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
 * NOTE: Iterates every gym and assigns a unique subset of equipment
 *       using firstOrCreate to prevent duplicate composite-key violations.
 *       Every status ENUM value (operational, maintenance, retired) is
 *       represented across the dataset.
 */
class GymInventorySeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $equipmentIds = Equipment::pluck('id')->toArray();
        $statuses     = ['operational', 'operational', 'operational', 'maintenance', 'retired'];

        Gym::all()->each(function (Gym $gym) use ($equipmentIds, $statuses): void {
            shuffle($equipmentIds);
            $selected = array_slice($equipmentIds, 0, min(count($equipmentIds), 12));

            foreach (array_unique($selected) as $index => $equipmentId) {
                \Illuminate\Support\Facades\DB::table('gym_inventory')->updateOrInsert(
                    ['gym_id' => $gym->id, 'equipment_id' => $equipmentId],
                    [
                        'quantity' => rand(1, 15),
                        'status'   => $statuses[$index % count($statuses)],
                    ]
                );
            }
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * Seeds the rooms table.
 *
 * SRP: Solely responsible for populating room records.
 * NOTE: Creates a standard set of rooms for each gym so every gym
 *       has at least one room before classes are seeded.
 */
class RoomSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $roomTemplates = [
            ['name' => 'Main Floor',      'capacity' => 50],
            ['name' => 'Spinning Hall',   'capacity' => 25],
            ['name' => 'Yoga Studio',     'capacity' => 20],
            ['name' => 'Boxing Area',     'capacity' => 15],
            ['name' => 'Functional Zone', 'capacity' => 30],
        ];

        Gym::all()->each(function (Gym $gym) use ($roomTemplates): void {
            foreach ($roomTemplates as $template) {
                Room::create(array_merge($template, ['gym_id' => $gym->id]));
            }
        });
    }
}

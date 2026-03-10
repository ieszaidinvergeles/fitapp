<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * Seeds the rooms table.
 *
 * SRP: Solely responsible for populating room records.
 */
class RoomSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        Room::factory()->count(15)->create();
    }
}

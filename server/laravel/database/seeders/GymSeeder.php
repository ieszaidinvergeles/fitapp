<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Seeder;

/**
 * Seeds the gyms table with two fixed demo gyms.
 *
 * SRP: Solely responsible for populating gym records.
 *
 * NOTE: manager_id is intentionally null here.
 *       DatabaseSeeder assigns it after users are created
 *       to resolve the circular gyms <-> users FK dependency.
 */
class GymSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $fixed = [
            [
                'name'            => 'FitApp Madrid Centro',
                'address'         => 'Calle Gran Vía 45',
                'city'            => 'Madrid',
                'phone'           => '+34 910 000 001',
                'location_coords' => '40.4200,-3.7050',
                'manager_id'      => null,
            ],
            [
                'name'            => 'FitApp Barcelona',
                'address'         => 'Avinguda Diagonal 160',
                'city'            => 'Barcelona',
                'phone'           => '+34 930 000 002',
                'location_coords' => '41.3951,2.1620',
                'manager_id'      => null,
            ],
        ];

        foreach ($fixed as $data) {
            Gym::create($data);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Seeder;

/**
 * Seeds the gyms table.
 *
 * SRP: Solely responsible for populating gym records.
 * NOTE: manager_id is assigned by DatabaseSeeder after users are created
 *       to resolve the circular gyms <-> users FK dependency.
 */
class GymSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $fixed = [
            ['name' => 'FitZone Madrid Centro',   'address' => 'Calle Gran Vía 45',          'city' => 'Madrid',    'phone' => '+34 910 000 001'],
            ['name' => 'FitZone Madrid Norte',    'address' => 'Calle Bravo Murillo 120',    'city' => 'Madrid',    'phone' => '+34 910 000 002'],
            ['name' => 'FitZone Barcelona',       'address' => 'Avinguda Diagonal 200',      'city' => 'Barcelona', 'phone' => '+34 930 000 001'],
            ['name' => 'FitZone Valencia',        'address' => 'Carrer de Colom 18',         'city' => 'Valencia',  'phone' => '+34 960 000 001'],
            ['name' => 'FitZone Sevilla',         'address' => 'Avenida de la Constitución 5','city' => 'Sevilla',  'phone' => '+34 950 000 001'],
            ['name' => 'FitZone Bilbao',          'address' => 'Calle Autonomía 30',         'city' => 'Bilbao',    'phone' => '+34 940 000 001'],
        ];

        foreach ($fixed as $data) {
            Gym::create(array_merge($data, [
                'manager_id'      => null,
                'location_coords' => '40.416775,-3.703790',
            ]));
        }

        Gym::factory()->count(4)->create();
    }
}

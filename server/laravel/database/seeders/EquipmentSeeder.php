<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

/**
 * Seeds the equipment table.
 *
 * SRP: Solely responsible for populating equipment records.
 * NOTE: Fixed records cover both home-accessible and gym-only equipment.
 */
class EquipmentSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $fixed = [
            ['name' => 'Barbell',          'description' => 'Standard Olympic barbell.',               'is_home_accessible' => false],
            ['name' => 'Dumbbell Set',      'description' => 'Adjustable dumbbell pair.',               'is_home_accessible' => true],
            ['name' => 'Kettlebell',        'description' => 'Cast iron kettlebell.',                   'is_home_accessible' => true],
            ['name' => 'Resistance Band',   'description' => 'Elastic band for activation work.',       'is_home_accessible' => true],
            ['name' => 'Pull-up Bar',       'description' => 'Doorframe or wall-mounted bar.',          'is_home_accessible' => true],
            ['name' => 'Yoga Mat',          'description' => 'Non-slip exercise mat.',                  'is_home_accessible' => true],
            ['name' => 'Treadmill',         'description' => 'Motorised running machine.',              'is_home_accessible' => false],
            ['name' => 'Rowing Machine',    'description' => 'Air or magnetic resistance rower.',       'is_home_accessible' => false],
            ['name' => 'Spin Bike',         'description' => 'Indoor cycling bike.',                    'is_home_accessible' => false],
            ['name' => 'Cable Machine',     'description' => 'Adjustable pulley cable station.',        'is_home_accessible' => false],
            ['name' => 'Smith Machine',     'description' => 'Guided barbell press and squat rack.',    'is_home_accessible' => false],
            ['name' => 'Battle Ropes',      'description' => 'Heavy rope for conditioning work.',       'is_home_accessible' => false],
            ['name' => 'Foam Roller',       'description' => 'Self-myofascial release tool.',           'is_home_accessible' => true],
            ['name' => 'Plyo Box',          'description' => 'Wooden box for jump training.',           'is_home_accessible' => false],
            ['name' => 'Suspension Trainer','description' => 'TRX-style bodyweight training system.',  'is_home_accessible' => true],
        ];

        foreach ($fixed as $data) {
            Equipment::create($data);
        }

        Equipment::factory()->count(5)->create();
    }
}

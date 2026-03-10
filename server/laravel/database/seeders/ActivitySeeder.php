<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

/**
 * Seeds the activities table.
 *
 * SRP: Solely responsible for populating activity records.
 * NOTE: Creates one fixed record per intensity_level ENUM value
 *       to guarantee all levels are covered.
 */
class ActivitySeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $fixed = [
            ['name' => 'Stretching',   'description' => 'Low impact flexibility work.',            'intensity_level' => 'low'],
            ['name' => 'Yoga',         'description' => 'Mind and body balance practice.',         'intensity_level' => 'low'],
            ['name' => 'Pilates',      'description' => 'Core strength and posture training.',     'intensity_level' => 'medium'],
            ['name' => 'Spinning',     'description' => 'Indoor cycling endurance session.',       'intensity_level' => 'medium'],
            ['name' => 'Zumba',        'description' => 'Dance-based cardio workout.',             'intensity_level' => 'medium'],
            ['name' => 'Swimming',     'description' => 'Full body aquatic training.',             'intensity_level' => 'medium'],
            ['name' => 'Boxing',       'description' => 'Striking technique and conditioning.',    'intensity_level' => 'high'],
            ['name' => 'HIIT',         'description' => 'High intensity interval training.',       'intensity_level' => 'high'],
            ['name' => 'CrossFit',     'description' => 'Varied functional movements at speed.',   'intensity_level' => 'high'],
            ['name' => 'Bodybuilding', 'description' => 'Hypertrophy-focused resistance work.',    'intensity_level' => 'high'],
            ['name' => 'Spartan Race', 'description' => 'Obstacle course race preparation.',       'intensity_level' => 'extreme'],
            ['name' => 'MMA Training', 'description' => 'Mixed martial arts conditioning.',        'intensity_level' => 'extreme'],
        ];

        foreach ($fixed as $data) {
            Activity::create($data);
        }

        Activity::factory()->count(8)->create();
    }
}

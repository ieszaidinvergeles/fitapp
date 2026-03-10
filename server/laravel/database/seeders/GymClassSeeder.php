<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Gym;
use App\Models\GymClass;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the classes table.
 *
 * SRP: Solely responsible for populating gym class records.
 * NOTE: Creates a realistic weekly schedule per gym covering all
 *       activities. Also creates a batch of cancelled classes to
 *       cover the is_cancelled boolean state.
 */
class GymClassSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $activities  = Activity::all();
        $instructors = User::where('role', 'staff')->get();

        Gym::all()->each(function (Gym $gym) use ($activities, $instructors): void {
            $rooms = Room::where('gym_id', $gym->id)->get();

            if ($rooms->isEmpty() || $instructors->isEmpty()) {
                return;
            }

            foreach ($activities->take(8) as $index => $activity) {
                $start = now()->addDays($index % 7)->setHour(8 + ($index * 2) % 12)->setMinute(0);

                GymClass::create([
                    'gym_id'         => $gym->id,
                    'activity_id'    => $activity->id,
                    'instructor_id'  => $instructors->random()->id,
                    'room_id'        => $rooms->random()->id,
                    'start_time'     => $start,
                    'end_time'       => (clone $start)->modify('+1 hour'),
                    'capacity_limit' => rand(10, 30),
                    'is_cancelled'   => false,
                ]);
            }

            GymClass::create([
                'gym_id'         => $gym->id,
                'activity_id'    => $activities->random()->id,
                'instructor_id'  => $instructors->random()->id,
                'room_id'        => $rooms->random()->id,
                'start_time'     => now()->subDays(2),
                'end_time'       => now()->subDays(2)->modify('+1 hour'),
                'capacity_limit' => 20,
                'is_cancelled'   => true,
            ]);
        });

        GymClass::factory()->count(20)->create();
    }
}

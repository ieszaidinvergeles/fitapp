<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Gym;
use App\Models\GymClass;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the classes table with a demo-ready schedule.
 *
 * SRP: Solely responsible for populating gym class records.
 *
 * Strategy:
 *   - Creates 3 classes today and 3 classes tomorrow for each gym,
 *     ensuring the staff dashboard "today_classes" and user dashboard
 *     "next_class" / "upcoming_bookings" always have live data.
 *   - One past class and one cancelled class are added per gym for
 *     history/status coverage.
 *   - Uses the fixed staff users (staff1, staff2) as instructors.
 */
class GymClassSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $activities  = Activity::all();
        $staff1      = User::where('email', 'staff1@fitapp.com')->first();
        $staff2      = User::where('email', 'staff2@fitapp.com')->first();

        if ($activities->isEmpty()) {
            return;
        }

        $activitySlice = $activities->take(6)->values();

        Gym::all()->each(function (Gym $gym) use ($activitySlice, $staff1, $staff2): void {
            $rooms = Room::where('gym_id', $gym->id)->get();

            if ($rooms->isEmpty() || !$staff1) {
                return;
            }

            $room1 = $rooms->get(0);
            $room2 = $rooms->get(1) ?? $room1;

            $instructors = array_filter([$staff1->id, $staff2?->id]);

            $schedule = [
                // Today morning
                [
                    'activity'   => $activitySlice->get(0),
                    'start'      => now()->setHour(9)->setMinute(0)->setSecond(0),
                    'room'       => $room1,
                    'instructor' => $instructors[0],
                    'capacity'   => 20,
                    'cancelled'  => false,
                ],
                // Today midday
                [
                    'activity'   => $activitySlice->get(1),
                    'start'      => now()->setHour(12)->setMinute(0)->setSecond(0),
                    'room'       => $room2,
                    'instructor' => $instructors[array_key_last($instructors)] ?? $instructors[0],
                    'capacity'   => 15,
                    'cancelled'  => false,
                ],
                // Today evening
                [
                    'activity'   => $activitySlice->get(2),
                    'start'      => now()->setHour(18)->setMinute(30)->setSecond(0),
                    'room'       => $room1,
                    'instructor' => $instructors[0],
                    'capacity'   => 25,
                    'cancelled'  => false,
                ],
                // Tomorrow morning
                [
                    'activity'   => $activitySlice->get(3),
                    'start'      => now()->addDay()->setHour(9)->setMinute(0)->setSecond(0),
                    'room'       => $room1,
                    'instructor' => $instructors[0],
                    'capacity'   => 20,
                    'cancelled'  => false,
                ],
                // Tomorrow afternoon
                [
                    'activity'   => $activitySlice->get(4),
                    'start'      => now()->addDay()->setHour(16)->setMinute(0)->setSecond(0),
                    'room'       => $room2,
                    'instructor' => $instructors[array_key_last($instructors)] ?? $instructors[0],
                    'capacity'   => 18,
                    'cancelled'  => false,
                ],
                // Past class (yesterday)
                [
                    'activity'   => $activitySlice->get(5),
                    'start'      => now()->subDay()->setHour(10)->setMinute(0)->setSecond(0),
                    'room'       => $room1,
                    'instructor' => $instructors[0],
                    'capacity'   => 20,
                    'cancelled'  => false,
                ],
                // Cancelled class
                [
                    'activity'   => $activitySlice->get(0),
                    'start'      => now()->addDays(2)->setHour(11)->setMinute(0)->setSecond(0),
                    'room'       => $room1,
                    'instructor' => $instructors[0],
                    'capacity'   => 20,
                    'cancelled'  => true,
                ],
            ];

            foreach ($schedule as $slot) {
                if (!$slot['activity']) {
                    continue;
                }

                $start = $slot['start'];
                $end   = (clone $start)->modify('+1 hour');

                GymClass::create([
                    'gym_id'         => $gym->id,
                    'activity_id'    => $slot['activity']->id,
                    'instructor_id'  => $slot['instructor'],
                    'room_id'        => $slot['room']->id,
                    'start_time'     => $start,
                    'end_time'       => $end,
                    'capacity_limit' => $slot['capacity'],
                    'is_cancelled'   => $slot['cancelled'],
                ]);
            }
        });
    }
}

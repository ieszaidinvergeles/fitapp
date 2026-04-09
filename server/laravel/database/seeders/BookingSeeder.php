<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\GymClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds the bookings table with demo-coherent records.
 *
 * SRP: Solely responsible for populating booking records.
 *
 * Strategy:
 *   - Assigns the two active demo clients (client1, client2) to the
 *     today/tomorrow classes so the user dashboard shows real upcoming bookings.
 *   - Adds one attended booking referencing the past class.
 *   - Adds one cancelled booking for status coverage.
 *   - All records reference classes created in GymClassSeeder.
 */
class BookingSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $client1   = User::where('email', 'client1@fitapp.com')->first();
        $client2   = User::where('email', 'client2@fitapp.com')->first();

        if (!$client1 || !$client2) {
            return;
        }

        $todayClasses = GymClass::where('is_cancelled', false)
            ->whereDate('start_time', today())
            ->orderBy('start_time')
            ->get();

        $tomorrowClasses = GymClass::where('is_cancelled', false)
            ->whereDate('start_time', today()->addDay())
            ->orderBy('start_time')
            ->get();

        $pastClass = GymClass::where('is_cancelled', false)
            ->where('start_time', '<', now())
            ->orderByDesc('start_time')
            ->first();

        foreach ($todayClasses->take(2) as $gymClass) {
            Booking::create([
                'class_id'     => $gymClass->id,
                'user_id'      => $client1->id,
                'status'       => 'active',
                'booked_at'    => Carbon::now()->subHours(rand(2, 24)),
                'cancelled_at' => null,
            ]);
        }

        if ($tomorrowClasses->isNotEmpty()) {
            Booking::create([
                'class_id'     => $tomorrowClasses->first()->id,
                'user_id'      => $client1->id,
                'status'       => 'active',
                'booked_at'    => Carbon::now()->subHour(),
                'cancelled_at' => null,
            ]);

            Booking::create([
                'class_id'     => $tomorrowClasses->first()->id,
                'user_id'      => $client2->id,
                'status'       => 'active',
                'booked_at'    => Carbon::now()->subHours(3),
                'cancelled_at' => null,
            ]);
        }

        if ($pastClass) {
            Booking::create([
                'class_id'     => $pastClass->id,
                'user_id'      => $client1->id,
                'status'       => 'attended',
                'booked_at'    => Carbon::now()->subDays(2),
                'cancelled_at' => null,
            ]);

            Booking::create([
                'class_id'     => $pastClass->id,
                'user_id'      => $client2->id,
                'status'       => 'no_show',
                'booked_at'    => Carbon::now()->subDays(2),
                'cancelled_at' => null,
            ]);
        }

        if ($todayClasses->count() >= 2) {
            Booking::create([
                'class_id'     => $todayClasses->get(1)->id,
                'user_id'      => $client2->id,
                'status'       => 'cancelled',
                'booked_at'    => Carbon::now()->subDays(1),
                'cancelled_at' => Carbon::now()->subHours(4),
            ]);
        }
    }
}

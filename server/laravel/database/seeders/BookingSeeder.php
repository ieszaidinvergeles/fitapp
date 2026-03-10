<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\GymClass;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the bookings table.
 *
 * SRP: Solely responsible for populating booking records.
 * NOTE: Creates fixed records covering all status ENUM values
 *       (active, cancelled, attended, no_show) then fills with factory data.
 */
class BookingSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $classes = GymClass::all();
        $clients = User::where('role', 'client')->get();

        if ($classes->isEmpty() || $clients->isEmpty()) {
            return;
        }

        $statuses = ['active', 'cancelled', 'attended', 'no_show'];

        foreach ($statuses as $status) {
            Booking::create([
                'class_id'     => $classes->random()->id,
                'user_id'      => $clients->random()->id,
                'status'       => $status,
                'booked_at'    => now()->subDays(rand(1, 30)),
                'cancelled_at' => $status === 'cancelled' ? now()->subDays(rand(1, 5)) : null,
            ]);
        }

        Booking::factory()->count(100)->create();
    }
}

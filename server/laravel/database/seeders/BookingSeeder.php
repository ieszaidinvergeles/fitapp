<?php

namespace Database\Seeders;

use App\Models\Booking;
use Illuminate\Database\Seeder;

/**
 * Seeds the bookings table.
 *
 * SRP: Solely responsible for populating booking records.
 */
class BookingSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        Booking::factory()->count(80)->create();
    }
}

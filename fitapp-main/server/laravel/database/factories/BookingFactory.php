<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\GymClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Booking.
 *
 * SRP: Solely responsible for generating fake Booking data.
 */
class BookingFactory extends Factory
{
    /** @var class-string<Booking> */
    protected $model = Booking::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'class_id'     => GymClass::inRandomOrder()->first()?->id,
            'user_id'      => User::where('role', 'client')->inRandomOrder()->first()?->id,
            'status'       => fake()->randomElement(['active', 'attended', 'cancelled', 'no_show']),
            'booked_at'    => fake()->dateTimeBetween('-30 days', 'now'),
            'cancelled_at' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Factory for User.
 *
 * SRP: Solely responsible for generating fake User data.
 * NOTE: password is stored in password_hash to match the DB schema.
 */
class UserFactory extends Factory
{
    /** @var class-string<User> */
    protected $model = User::class;

    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'username'                => substr(fake()->unique()->userName(), 0, 20),
            'email'                   => fake()->unique()->safeEmail(),
            'password_hash'           => Hash::make('password'),
            'role'                    => fake()->randomElement(['client', 'client', 'client', 'assistant', 'staff', 'manager', 'user_online']),
            'full_name'               => fake()->name(),
            'dni'                     => strtoupper(fake()->bothify('########?')),
            'birth_date'              => fake()->dateTimeBetween('-55 years', '-18 years')->format('Y-m-d'),
            'profile_photo_url'       => fake()->imageUrl(160, 160, 'people'),
            'current_gym_id'          => Gym::inRandomOrder()->first()?->id,
            'membership_plan_id'      => MembershipPlan::inRandomOrder()->first()?->id,
            'membership_status'       => fake()->randomElement(['active', 'active', 'paused', 'expired']),
            'cancellation_strikes'    => fake()->numberBetween(0, 3),
            'is_blocked_from_booking' => false,
        ];
    }

    /**
     * State for a client user.
     *
     * @return static
     */
    public function client(): static
    {
        return $this->state(['role' => 'client']);
    }

    /**
     * State for a staff user.
     *
     * @return static
     */
    public function staff(): static
    {
        return $this->state(['role' => 'staff']);
    }

    /**
     * State for an assistant user.
     *
     * @return static
     */
    public function assistant(): static
    {
        return $this->state(['role' => 'assistant']);
    }

    /**
     * State for a manager user.
     *
     * @return static
     */
    public function manager(): static
    {
        return $this->state(['role' => 'manager']);
    }

    /**
     * State for an admin user.
     *
     * @return static
     */
    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    /**
     * State for an online user.
     *
     * @return static
     */
    public function userOnline(): static
    {
        return $this->state(['role' => 'user_online', 'current_gym_id' => null]);
    }
}

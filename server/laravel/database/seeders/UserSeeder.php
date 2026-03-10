<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the users table.
 *
 * SRP: Solely responsible for populating user records.
 * NOTE: Creates fixed accounts for every role so all ENUM values
 *       are guaranteed to exist before other seeders run.
 */
class UserSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        User::create([
            'username'                => 'admin',
            'email'                   => 'admin@gymapp.test',
            'password_hash'           => Hash::make('password'),
            'role'                    => 'admin',
            'full_name'               => 'Admin Principal',
            'dni'                     => '00000001A',
            'membership_status'       => 'active',
            'cancellation_strikes'    => 0,
            'is_blocked_from_booking' => false,
        ]);

        User::create([
            'username'                => 'admin2',
            'email'                   => 'admin2@gymapp.test',
            'password_hash'           => Hash::make('password'),
            'role'                    => 'admin',
            'full_name'               => 'Admin Secundario',
            'dni'                     => '00000002A',
            'membership_status'       => 'active',
            'cancellation_strikes'    => 0,
            'is_blocked_from_booking' => false,
        ]);

        User::factory()->manager()->count(6)->create();
        User::factory()->staff()->count(15)->create();
        User::factory()->client()->count(40)->create();
        User::factory()->userOnline()->count(10)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the users table with fixed demo-ready accounts.
 *
 * SRP: Solely responsible for populating user records with predictable,
 *      demo-presentable credentials covering every role in the system.
 *
 * Fixed credentials (all passwords = "password"):
 *   admin@fitapp.com     — admin
 *   manager@fitapp.com   — manager   (assigned to gym in DatabaseSeeder)
 *   staff1@fitapp.com    — staff
 *   staff2@fitapp.com    — staff
 *   client1@fitapp.com   — client    (active membership)
 *   client2@fitapp.com   — client    (active membership)
 *   client3@fitapp.com   — client    (expired membership)
 *   online@fitapp.com    — user_online
 */
class UserSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $fixed = [
            [
                'username'                => 'admin',
                'email'                   => 'admin@fitapp.com',
                'password_hash'           => Hash::make('password'),
                'role'                    => 'admin',
                'full_name'               => 'Carlos Admin',
                'dni'                     => '00000001A',
                'birth_date'              => '1985-03-15',
                'membership_status'       => 'active',
                'cancellation_strikes'    => 0,
                'is_blocked_from_booking' => false,
            ],
            [
                'username'                => 'manager1',
                'email'                   => 'manager@fitapp.com',
                'password_hash'           => Hash::make('password'),
                'role'                    => 'manager',
                'full_name'               => 'Laura Gestora',
                'dni'                     => '00000002B',
                'birth_date'              => '1990-07-22',
                'membership_status'       => 'active',
                'cancellation_strikes'    => 0,
                'is_blocked_from_booking' => false,
            ],
            [
                'username'                => 'staff1',
                'email'                   => 'staff1@fitapp.com',
                'password_hash'           => Hash::make('password'),
                'role'                    => 'staff',
                'full_name'               => 'Miguel Instructor',
                'dni'                     => '00000003C',
                'birth_date'              => '1992-11-08',
                'membership_status'       => 'active',
                'cancellation_strikes'    => 0,
                'is_blocked_from_booking' => false,
            ],
            [
                'username'                => 'staff2',
                'email'                   => 'staff2@fitapp.com',
                'password_hash'           => Hash::make('password'),
                'role'                    => 'staff',
                'full_name'               => 'Ana Monitora',
                'dni'                     => '00000004D',
                'birth_date'              => '1994-04-30',
                'membership_status'       => 'active',
                'cancellation_strikes'    => 0,
                'is_blocked_from_booking' => false,
            ],
            [
                'username'                => 'client1',
                'email'                   => 'client1@fitapp.com',
                'password_hash'           => Hash::make('password'),
                'role'                    => 'client',
                'full_name'               => 'Pedro Cliente',
                'dni'                     => '00000005E',
                'birth_date'              => '1995-06-12',
                'membership_status'       => 'active',
                'cancellation_strikes'    => 0,
                'is_blocked_from_booking' => false,
            ],
            [
                'username'                => 'client2',
                'email'                   => 'client2@fitapp.com',
                'password_hash'           => Hash::make('password'),
                'role'                    => 'client',
                'full_name'               => 'Sofia Usuaria',
                'dni'                     => '00000006F',
                'birth_date'              => '1998-01-19',
                'membership_status'       => 'active',
                'cancellation_strikes'    => 1,
                'is_blocked_from_booking' => false,
            ],
            [
                'username'                => 'client3',
                'email'                   => 'client3@fitapp.com',
                'password_hash'           => Hash::make('password'),
                'role'                    => 'client',
                'full_name'               => 'Jorge Caducado',
                'dni'                     => '00000007G',
                'birth_date'              => '1988-09-05',
                'membership_status'       => 'expired',
                'cancellation_strikes'    => 2,
                'is_blocked_from_booking' => false,
            ],
            [
                'username'                => 'online1',
                'email'                   => 'online@fitapp.com',
                'password_hash'           => Hash::make('password'),
                'role'                    => 'user_online',
                'full_name'               => 'Elena Online',
                'dni'                     => '00000008H',
                'birth_date'              => '2000-12-01',
                'membership_status'       => 'active',
                'cancellation_strikes'    => 0,
                'is_blocked_from_booking' => false,
            ],
        ];

        foreach ($fixed as $data) {
            User::create($data);
        }
    }
}

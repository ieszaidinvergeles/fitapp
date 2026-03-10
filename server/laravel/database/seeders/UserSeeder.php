<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the users table.
 *
 * SRP: Solely responsible for populating user records.
 */
class UserSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        User::factory()->admin()->create([
            'username'  => 'admin',
            'email'     => 'admin@gym.test',
        ]);

        User::factory()->manager()->count(5)->create();
        User::factory()->staff()->count(10)->create();
        User::factory()->client()->count(50)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Routine;
use App\Models\User;
use App\Models\UserActiveRoutine;
use Illuminate\Database\Seeder;

/**
 * Seeds the user_active_routines pivot table.
 *
 * SRP: Solely responsible for populating user active routine records.
 * NOTE: Each client gets 1 active routine and optionally 1 inactive one
 *       to cover both boolean states of is_active.
 */
class UserActiveRoutineSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $routines = Routine::all();

        if ($routines->isEmpty()) {
            return;
        }

        User::where('role', 'client')->get()
            ->each(function (User $user) use ($routines): void {
                UserActiveRoutine::firstOrCreate(
                    ['user_id' => $user->id, 'routine_id' => $routines->random()->id],
                    ['is_active' => true, 'start_date' => now()->subDays(rand(1, 30))->format('Y-m-d')]
                );

                if (rand(0, 1)) {
                    $second = $routines->random();
                    UserActiveRoutine::firstOrCreate(
                        ['user_id' => $user->id, 'routine_id' => $second->id],
                        ['is_active' => false, 'start_date' => now()->subDays(rand(31, 90))->format('Y-m-d')]
                    );
                }
            });
    }
}

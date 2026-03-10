<?php

namespace Database\Seeders;

use App\Models\Routine;
use App\Models\User;
use App\Models\UserActiveRoutine;
use Illuminate\Database\Seeder;

/**
 * Seeds the user_active_routines table.
 *
 * SRP: Solely responsible for populating user-active-routine records.
 * NOTE: Uses firstOrCreate to respect the composite PK constraint.
 */
class UserActiveRoutineSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $routineIds = Routine::pluck('id')->toArray();

        User::where('role', 'client')->limit(20)->get()
            ->each(function (User $user) use ($routineIds): void {
                shuffle($routineIds);

                foreach (array_slice($routineIds, 0, rand(1, 3)) as $routineId) {
                    UserActiveRoutine::firstOrCreate(
                        ['user_id' => $user->id, 'routine_id' => $routineId],
                        ['is_active' => true, 'start_date' => now()->subDays(rand(1, 30))->format('Y-m-d')]
                    );
                }
            });
    }
}

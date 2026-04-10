<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Routine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the routine_exercises pivot table.
 *
 * SRP: Solely responsible for populating routine exercise records.
 * NOTE: Each routine gets a unique set of 5 to 8 exercises in order.
 *       updateOrInsert prevents composite-key violations on re-seed.
 */
class RoutineExerciseSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $exerciseIds = Exercise::pluck('id')->toArray();

        Routine::all()->each(function (Routine $routine) use ($exerciseIds): void {
            shuffle($exerciseIds);
            $count    = rand(5, 8);
            $selected = array_unique(array_slice($exerciseIds, 0, $count));

            foreach (array_values($selected) as $index => $exerciseId) {
                DB::table('routine_exercises')->updateOrInsert(
                    ['routine_id' => $routine->id, 'exercise_id' => $exerciseId],
                    [
                        'order_index'      => $index + 1,
                        'recommended_sets' => rand(3, 5),
                        'recommended_reps' => rand(6, 15),
                        'rest_seconds'     => collect([30, 60, 90, 120])->random(),
                    ]
                );
            }
        });
    }
}

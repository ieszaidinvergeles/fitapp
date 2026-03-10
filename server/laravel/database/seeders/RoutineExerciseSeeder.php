<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Routine;
use App\Models\RoutineExercise;
use Illuminate\Database\Seeder;

/**
 * Seeds the routine_exercises table.
 *
 * SRP: Solely responsible for populating routine exercise pivot records.
 * NOTE: Iterates routines and assigns unique exercises to avoid
 *       composite-key violations.
 */
class RoutineExerciseSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $exerciseIds = Exercise::pluck('id')->toArray();

        Routine::all()->each(function (Routine $routine) use ($exerciseIds): void {
            shuffle($exerciseIds);
            $selected = array_slice($exerciseIds, 0, rand(4, 8));

            foreach (array_values(array_unique($selected)) as $index => $exerciseId) {
                RoutineExercise::firstOrCreate(
                    ['routine_id' => $routine->id, 'exercise_id' => $exerciseId],
                    [
                        'order_index'       => $index + 1,
                        'recommended_sets'  => rand(3, 5),
                        'recommended_reps'  => rand(8, 15),
                        'rest_seconds'      => 60,
                    ]
                );
            }
        });
    }
}

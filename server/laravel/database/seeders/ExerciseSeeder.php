<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

/**
 * Seeds the exercises table.
 *
 * SRP: Solely responsible for populating exercise records.
 * NOTE: Fixed records cover all 20 target_muscle_group ENUM values.
 */
class ExerciseSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $fixed = [
            ['name' => 'Bench Press',        'target_muscle_group' => 'chest',        'description' => 'Horizontal push compound movement.'],
            ['name' => 'Cable Fly',          'target_muscle_group' => 'chest',        'description' => 'Chest isolation with constant tension.'],
            ['name' => 'Bent-over Row',      'target_muscle_group' => 'upper_back',   'description' => 'Horizontal pull compound movement.'],
            ['name' => 'Face Pull',          'target_muscle_group' => 'upper_back',   'description' => 'Rear delt and upper back cable exercise.'],
            ['name' => 'Deadlift',           'target_muscle_group' => 'lower_back',   'description' => 'Full posterior chain compound lift.'],
            ['name' => 'Back Extension',     'target_muscle_group' => 'lower_back',   'description' => 'Lumbar spine isolation movement.'],
            ['name' => 'Overhead Press',     'target_muscle_group' => 'shoulders',    'description' => 'Vertical push compound movement.'],
            ['name' => 'Lateral Raise',      'target_muscle_group' => 'shoulders',    'description' => 'Side delt isolation exercise.'],
            ['name' => 'Bicep Curl',         'target_muscle_group' => 'biceps',       'description' => 'Elbow flexion isolation.'],
            ['name' => 'Hammer Curl',        'target_muscle_group' => 'biceps',       'description' => 'Neutral grip curl for brachialis.'],
            ['name' => 'Tricep Pushdown',    'target_muscle_group' => 'triceps',      'description' => 'Elbow extension isolation.'],
            ['name' => 'Skull Crusher',      'target_muscle_group' => 'triceps',      'description' => 'Lying tricep extension.'],
            ['name' => 'Wrist Curl',         'target_muscle_group' => 'forearms',     'description' => 'Wrist flexion and extension exercise.'],
            ['name' => 'Plank',              'target_muscle_group' => 'core',         'description' => 'Isometric core stability hold.'],
            ['name' => 'Crunch',             'target_muscle_group' => 'core',         'description' => 'Spinal flexion abdominal exercise.'],
            ['name' => 'Russian Twist',      'target_muscle_group' => 'obliques',     'description' => 'Rotational oblique exercise.'],
            ['name' => 'Side Plank',         'target_muscle_group' => 'obliques',     'description' => 'Lateral core stability hold.'],
            ['name' => 'Squat',              'target_muscle_group' => 'quadriceps',   'description' => 'Vertical push lower body compound.'],
            ['name' => 'Leg Press',          'target_muscle_group' => 'quadriceps',   'description' => 'Machine-based quad dominant press.'],
            ['name' => 'Romanian Deadlift',  'target_muscle_group' => 'hamstrings',   'description' => 'Hip hinge hamstring dominant lift.'],
            ['name' => 'Leg Curl',           'target_muscle_group' => 'hamstrings',   'description' => 'Knee flexion isolation machine.'],
            ['name' => 'Hip Thrust',         'target_muscle_group' => 'glutes',       'description' => 'Hip extension glute isolation.'],
            ['name' => 'Glute Kickback',     'target_muscle_group' => 'glutes',       'description' => 'Cable or bodyweight glute extension.'],
            ['name' => 'Calf Raise',         'target_muscle_group' => 'calves',       'description' => 'Plantar flexion isolation exercise.'],
            ['name' => 'Seated Calf Raise',  'target_muscle_group' => 'calves',       'description' => 'Soleus focused calf exercise.'],
            ['name' => 'Mountain Climber',   'target_muscle_group' => 'hip_flexors',  'description' => 'Dynamic hip flexor and core exercise.'],
            ['name' => 'Leg Raise',          'target_muscle_group' => 'hip_flexors',  'description' => 'Hanging or lying hip flexion.'],
            ['name' => 'Sumo Squat',         'target_muscle_group' => 'adductors',    'description' => 'Wide stance squat targeting inner thigh.'],
            ['name' => 'Cable Abduction',    'target_muscle_group' => 'abductors',    'description' => 'Hip abduction cable exercise.'],
            ['name' => 'Shrug',              'target_muscle_group' => 'traps',        'description' => 'Scapular elevation trap exercise.'],
            ['name' => 'Pull-up',            'target_muscle_group' => 'lats',         'description' => 'Vertical pull bodyweight compound.'],
            ['name' => 'Lat Pulldown',       'target_muscle_group' => 'lats',         'description' => 'Vertical pull cable machine exercise.'],
            ['name' => 'Neck Extension',     'target_muscle_group' => 'neck',         'description' => 'Cervical spine extension exercise.'],
            ['name' => 'Burpee',             'target_muscle_group' => 'full_body',    'description' => 'Full body explosive conditioning exercise.'],
            ['name' => 'Clean and Press',    'target_muscle_group' => 'full_body',    'description' => 'Olympic lift compound movement.'],
        ];

        foreach ($fixed as $data) {
            Exercise::create(array_merge($data, [
                'image_url' => null,
                'video_url' => null,
            ]));
        }

        Exercise::factory()->count(10)->create();
    }
}

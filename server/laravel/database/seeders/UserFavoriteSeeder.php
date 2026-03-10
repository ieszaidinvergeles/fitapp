<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Gym;
use App\Models\Routine;
use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Database\Seeder;

/**
 * Seeds the user_favorites table.
 *
 * SRP: Solely responsible for populating user favourite records.
 * NOTE: Covers all entity_type ENUM values (gym, activity, routine)
 *       for each client user.
 */
class UserFavoriteSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $gyms       = Gym::pluck('id');
        $activities = Activity::pluck('id');
        $routines   = Routine::pluck('id');

        User::where('role', 'client')->get()
            ->each(function (User $user) use ($gyms, $activities, $routines): void {
                UserFavorite::firstOrCreate(['user_id' => $user->id, 'entity_type' => 'gym',      'entity_id' => $gyms->random()]);
                UserFavorite::firstOrCreate(['user_id' => $user->id, 'entity_type' => 'activity', 'entity_id' => $activities->random()]);
                UserFavorite::firstOrCreate(['user_id' => $user->id, 'entity_type' => 'routine',  'entity_id' => $routines->random()]);

                if (rand(0, 1)) {
                    UserFavorite::firstOrCreate(['user_id' => $user->id, 'entity_type' => 'gym',      'entity_id' => $gyms->random()]);
                }
                if (rand(0, 1)) {
                    UserFavorite::firstOrCreate(['user_id' => $user->id, 'entity_type' => 'activity', 'entity_id' => $activities->random()]);
                }
            });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Root database seeder – orchestrates all seeders in dependency order.
 *
 * SRP: Solely responsible for defining the seeding execution order.
 * OCP: Add new seeders to the $seeders list without modifying existing logic.
 * DIP: Depends on Seeder abstractions; each child seeder is independently
 *      responsible for its own table.
 *
 * Execution order:
 *   1. Seed tables with no foreign keys (leaf nodes of the dependency graph).
 *   2. Seed tables whose dependencies are already populated.
 *   3. Resolve the gyms <-> users circular FK after both exist.
 */
class DatabaseSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        // Step 1 – tables with no FK dependencies
        $this->call([
            MembershipPlanSeeder::class,
            ActivitySeeder::class,
            DietPlanSeeder::class,
            EquipmentSeeder::class,
            ExerciseSeeder::class,
        ]);

        // Step 2 – gyms first (without manager_id), then users
        $this->call(GymSeeder::class);
        $this->call(UserSeeder::class);

        // Step 3 – resolve circular FK: assign a manager to each gym
        Gym::all()->each(function (Gym $gym): void {
            $manager = User::where('role', 'manager')->inRandomOrder()->first();
            if ($manager) {
                $gym->update(['manager_id' => $manager->id]);
            }
        });

        // Step 4 – seed remaining tables in dependency order
        $this->call([
            RoomSeeder::class,
            GymInventorySeeder::class,
            RecipeSeeder::class,
            RoutineSeeder::class,
            RoutineExerciseSeeder::class,
            GymClassSeeder::class,
            BookingSeeder::class,
            BodyMetricSeeder::class,
            AppNotificationSeeder::class,
            SettingSeeder::class,
            StaffAttendanceSeeder::class,
            UserActiveRoutineSeeder::class,
            UserFavoriteSeeder::class,
            UserMealScheduleSeeder::class,
            UserPartnerSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Root database seeder — orchestrates all seeders in dependency order.
 *
 * SRP: Solely responsible for defining the seeding execution order.
 * OCP: Add new seeders to the call list without modifying existing logic.
 * DIP: Depends on individual seeder abstractions, each independently
 *      responsible for its own table.
 *
 * Execution order:
 *   Step 1 — Tables with no FK dependencies (leaf nodes).
 *   Step 2 — gyms first (manager_id null), then users.
 *   Step 3 — Resolve circular FK: assign manager_id to each gym.
 *   Step 4 — All remaining tables in FK dependency order.
 */
class DatabaseSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $this->call([
            MembershipPlanSeeder::class,
            ActivitySeeder::class,
            DietPlanSeeder::class,
            EquipmentSeeder::class,
            ExerciseSeeder::class,
        ]);

        $this->call(GymSeeder::class);
        $this->call(UserSeeder::class);

        Gym::all()->each(function (Gym $gym): void {
            $manager = User::where('role', 'manager')->inRandomOrder()->first();
            if ($manager) {
                $gym->update(['manager_id' => $manager->id]);
            }
        });

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

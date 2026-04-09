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
 *   Step 2 — Gyms first (manager_id null), then users.
 *   Step 3 — Resolve circular FK: assign manager_id and current_gym_id
 *             for the fixed demo users.
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

        $gym1 = Gym::first();
        $gym2 = Gym::skip(1)->first() ?? $gym1;

        $manager = User::where('email', 'manager@fitapp.com')->first();
        $staff1  = User::where('email', 'staff1@fitapp.com')->first();
        $staff2  = User::where('email', 'staff2@fitapp.com')->first();

        if ($gym1 && $manager) {
            $gym1->update(['manager_id' => $manager->id]);
            $manager->update(['current_gym_id' => $gym1->id]);
        }

        if ($gym2 && $gym2->id !== $gym1?->id) {
            $gym2->update(['manager_id' => $manager?->id]);
        }

        $staffGymId = $gym1?->id;

        if ($staff1) {
            $staff1->update(['current_gym_id' => $staffGymId]);
        }

        if ($staff2) {
            $staff2->update(['current_gym_id' => $staffGymId]);
        }

        $gymForClients = $gym1?->id;
        $planId        = \App\Models\MembershipPlan::where('type', 'physical')
            ->orderBy('price')
            ->first()
            ?->id;

        foreach (['client1@fitapp.com', 'client2@fitapp.com', 'admin@fitapp.com'] as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'current_gym_id'     => $gymForClients,
                    'membership_plan_id' => $planId,
                ]);
            }
        }

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

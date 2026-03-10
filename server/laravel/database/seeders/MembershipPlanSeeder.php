<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

/**
 * Seeds the membership_plans table.
 *
 * SRP: Solely responsible for populating membership plan records.
 */
class MembershipPlanSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        MembershipPlan::factory()->count(5)->create();
    }
}

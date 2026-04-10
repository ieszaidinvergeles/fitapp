<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

/**
 * Seeds the membership_plans table.
 *
 * SRP: Solely responsible for populating membership plan records.
 * NOTE: Creates one fixed record per plan type to ensure all ENUM
 *       combinations (physical, online, duo) are represented,
 *       then fills the rest with factory data.
 */
class MembershipPlanSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        MembershipPlan::create([
            'name'               => 'Basic Physical',
            'type'               => 'physical',
            'allow_partner_link' => false,
            'price'              => 19.99,
        ]);

        MembershipPlan::create([
            'name'               => 'Pro Physical',
            'type'               => 'physical',
            'allow_partner_link' => false,
            'price'              => 39.99,
        ]);

        MembershipPlan::create([
            'name'               => 'Elite Physical',
            'type'               => 'physical',
            'allow_partner_link' => false,
            'price'              => 59.99,
        ]);

        MembershipPlan::create([
            'name'               => 'Online Basic',
            'type'               => 'online',
            'allow_partner_link' => false,
            'price'              => 9.99,
        ]);

        MembershipPlan::create([
            'name'               => 'Online Plus',
            'type'               => 'online',
            'allow_partner_link' => false,
            'price'              => 19.99,
        ]);

        MembershipPlan::create([
            'name'               => 'Duo Premium',
            'type'               => 'duo',
            'allow_partner_link' => true,
            'price'              => 69.99,
        ]);

        MembershipPlan::create([
            'name'               => 'Duo Standard',
            'type'               => 'duo',
            'allow_partner_link' => true,
            'price'              => 49.99,
        ]);

    }
}

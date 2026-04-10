<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the user_partners table.
 *
 * SRP: Solely responsible for populating partner link records.
 * NOTE: Identifies users with a "duo" type membership plan and links
 *       them together as testing pairs.
 */
class UserPartnerSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $duoPlanIds = MembershipPlan::where('type', 'duo')->pluck('id')->toArray();
        $eligible = User::whereIn('membership_plan_id', $duoPlanIds)->get();

        if ($eligible->count() < 2) {
            return;
        }

        $chunks = $eligible->chunk(2);
        foreach ($chunks as $pair) {
            if ($pair->count() === 2) {
                $primary = $pair->first();
                $secondary = $pair->last();

                DB::table('user_partners')->insert([
                    'primary_user_id'   => $primary->id,
                    'secondary_user_id' => $secondary->id,
                    'status'            => 'active',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use App\Models\User;
use App\Models\UserPartner;
use Illuminate\Database\Seeder;

/**
 * Seeds the user_partners table.
 *
 * SRP: Solely responsible for populating user partner link records.
 * NOTE: Only links users who have a duo membership plan to keep
 *       data coherent with allow_partner_link business rule.
 */
class UserPartnerSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        $duoPlanIds = MembershipPlan::where('allow_partner_link', true)->pluck('id');

        $eligible = User::whereIn('membership_plan_id', $duoPlanIds)->get();

        if ($eligible->count() < 2) {
            UserPartner::factory()->count(10)->create();
            return;
        }

        $paired = collect();

        $eligible->each(function (User $user) use ($eligible, $paired): void {
            if ($paired->contains($user->id)) {
                return;
            }

            $partner = $eligible
                ->where('id', '!=', $user->id)
                ->whereNotIn('id', $paired->toArray())
                ->first();

            if (!$partner) {
                return;
            }

            UserPartner::firstOrCreate(
                ['primary_user_id' => $user->id, 'partner_user_id' => $partner->id],
                ['linked_at' => now()->subDays(rand(1, 180))]
            );

            $paired->push($user->id);
            $paired->push($partner->id);
        });
    }
}

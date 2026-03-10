<?php

namespace Database\Seeders;

use App\Models\UserPartner;
use Illuminate\Database\Seeder;

/**
 * Seeds the user_partners table.
 *
 * SRP: Solely responsible for populating user-partner link records.
 */
class UserPartnerSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        UserPartner::factory()->count(10)->create();
    }
}

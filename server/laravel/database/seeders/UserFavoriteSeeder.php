<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Database\Seeder;

/**
 * Seeds the user_favorites table.
 *
 * SRP: Solely responsible for populating user favourite records.
 */
class UserFavoriteSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        UserFavorite::factory()->count(40)->create();
    }
}

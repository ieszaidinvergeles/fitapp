<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;

/**
 * Seeds the recipes table.
 *
 * SRP: Solely responsible for populating recipe records.
 */
class RecipeSeeder extends Seeder
{
    /** @inheritdoc */
    public function run(): void
    {
        Recipe::factory()->count(30)->create();
    }
}

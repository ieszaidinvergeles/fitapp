<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the diet_plan_recipes pivot table.
 *
 * SRP: Solely responsible for the diet_plan_recipes schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('diet_plan_recipes', function (Blueprint $table) {
            $table->foreignId('diet_plan_id');
            $table->foreignId('recipe_id');
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack', 'pre_workout', 'post_workout']);
            $table->primary(['diet_plan_id', 'recipe_id', 'meal_type']);

            $table->foreign('diet_plan_id')->references('id')->on('diet_plans')->cascadeOnDelete();
            $table->foreign('recipe_id')->references('id')->on('recipes')->cascadeOnDelete();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('diet_plan_recipes');
    }
};

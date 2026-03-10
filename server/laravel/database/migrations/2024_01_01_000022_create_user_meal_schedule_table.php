<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the user_meal_schedule table.
 *
 * SRP: Solely responsible for the user_meal_schedule schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('user_meal_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');               // ->constrained('users')->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack', 'pre_workout', 'post_workout'])->nullable();
            $table->foreignId('recipe_id')->nullable(); // ->constrained('recipes')->nullOnDelete();
            $table->boolean('is_consumed')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('recipe_id')->references('id')->on('recipes');

        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('user_meal_schedule');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the recipes table.
 *
 * SRP: Solely responsible for the recipes schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->text('description');
            $table->text('ingredients');
            $table->text('preparation_steps');
            $table->integer('calories');
            $table->json('macros_json')->nullable()->comment('{protein: 30, carbs: 50, fat: 10}');
            $table->enum('type', ['breakfast', 'lunch', 'dinner', 'snack', 'pre_workout', 'post_workout']);
            $table->string('image_url')->nullable();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};

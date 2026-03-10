<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the routine_exercises pivot table.
 *
 * SRP: Solely responsible for the routine_exercises schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('routine_exercises', function (Blueprint $table) {
            $table->foreignId('routine_id');
            $table->foreignId('exercise_id');
            $table->integer('order_index');
            $table->integer('recommended_sets');
            $table->integer('recommended_reps');
            $table->integer('rest_seconds');
            $table->primary(['routine_id', 'exercise_id']);

            $table->foreign('routine_id')->references('id')->on('routines')->cascadeOnDelete();
            $table->foreign('exercise_id')->references('id')->on('exercises')->cascadeOnDelete();

        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('routine_exercises');
    }
};

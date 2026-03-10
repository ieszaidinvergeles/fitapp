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
            $table->foreignId('routine_id');  // ->constrained('routines')->cascadeOnDelete();
            $table->foreignId('exercise_id'); // ->constrained('exercises')->cascadeOnDelete();
            $table->integer('order_index')->nullable();
            $table->integer('recommended_sets', 2)->nullable();
            $table->integer('recommended_reps', 2)->nullable();
            $table->integer('rest_seconds', 3)->nullable();
            $table->primary(['routine_id', 'exercise_id']);

            $table->foreign('routine_id')->references('id')->on('routines');
            $table->foreign('exercise_id')->references('id')->on('exercises');

        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('routine_exercises');
    }
};

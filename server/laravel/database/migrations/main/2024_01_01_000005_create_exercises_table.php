<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the exercises table.
 *
 * SRP: Solely responsible for the exercises schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name',80);
            $table->text('description');
            $table->string('image_url', 500)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->enum('target_muscle_group', ['chest','upper_back','lower_back','shoulders','biceps','triceps','forearms','core','obliques','quadriceps','hamstrings','glutes','calves','hip_flexors','adductors','abductors','traps','lats','neck','full_body',])->nullable();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};

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
            $table->string('name')->nullable();
            $table->text('description', 280)->nullable();
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('target_muscle_group')->nullable();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};

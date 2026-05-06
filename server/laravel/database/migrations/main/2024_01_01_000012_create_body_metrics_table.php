<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the body_metrics table.
 *
 * SRP: Solely responsible for the body_metrics schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('body_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->date('date');
            $table->decimal('weight_kg', 5, 1);
            $table->decimal('height_cm', 5, 1);
            $table->decimal('body_fat_pct', 6, 2)->nullable();
            $table->decimal('muscle_mass_pct', 6, 2)->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('body_metrics');
    }
};

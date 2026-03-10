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
            // $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id');
            $table->date('date')->nullable();
            $table->decimal('weight_kg', 10, 1)->nullable();
            $table->decimal('height_cm', 10, 0)->nullable();
            $table->decimal('body_fat_pct', 10, 2)->nullable();
            $table->decimal('muscle_mass_pct', 10, 2)->nullable();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('body_metrics');
    }
};

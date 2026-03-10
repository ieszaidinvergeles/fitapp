<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the settings table with user_id as the primary key (1-to-1).
 *
 * SRP: Solely responsible for the settings schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->boolean('share_workout_stats')->default(true)->comment('Share weights lifted/reps');
            $table->boolean('share_body_metrics')->default(false)->comment('Share weight/fat %');
            $table->boolean('share_attendance')->default(true);
            $table->boolean('theme_preference')->default(false)->comment('false->light or true->dark');
            $table->string('language_preference')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

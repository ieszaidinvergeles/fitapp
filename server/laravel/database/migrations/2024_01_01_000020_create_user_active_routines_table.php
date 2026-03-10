<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the user_active_routines pivot table.
 *
 * SRP: Solely responsible for the user_active_routines schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('user_active_routines', function (Blueprint $table) {
            $table->foreignId('user_id');    // ->constrained('users')->cascadeOnDelete();
            $table->foreignId('routine_id'); // ->constrained('routines')->cascadeOnDelete();
            $table->boolean('is_active')->nullable();
            $table->date('start_date')->nullable();
            $table->primary(['user_id', 'routine_id']);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('routine_id')->references('id')->on('routines');

        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('user_active_routines');
    }
};

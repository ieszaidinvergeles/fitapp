<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the staff_attendance table.
 *
 * SRP: Solely responsible for the staff_attendance schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->nullable(); // ->constrained('users')->nullOnDelete();
            $table->foreignId('gym_id')->nullable();   // ->constrained('gyms')->nullOnDelete();
            $table->timestamp('clock_in')->useCurrent();
            $table->timestamp('clock_out')->nullable();
            $table->date('date')->nullable();

            $table->foreign('staff_id')->references('id')->on('users');
            $table->foreign('gym_id')->references('id')->on('gyms');

        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
    }
};

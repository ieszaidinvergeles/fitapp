<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the classes table.
 *
 * SRP: Solely responsible for the classes schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id');        // ->nullable()->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('activity_id');   // ->nullable()->constrained('activities')->nullOnDelete();
            $table->foreignId('instructor_id'); // ->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('room_id');       // ->nullable()->constrained('rooms')->nullOnDelete();
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->integer('capacity_limit')->nullable();
            $table->boolean('is_cancelled')->default(false);

            $table->foreign('gym_id')->references('id')->on('gyms');
            $table->foreign('activity_id')->references('id')->on('activities');
            $table->foreign('instructor_id')->references('id')->on('users');
            $table->foreign('room_id')->references('id')->on('rooms');
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the bookings table.
 *
 * SRP: Solely responsible for the bookings schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->enum('status', ['active', 'cancelled', 'attended', 'no_show']);
            $table->timestamp('booked_at')->useCurrent();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

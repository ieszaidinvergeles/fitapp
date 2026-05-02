<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the booking_history table.
 *
 * SRP: Solely responsible for defining the booking status history schema.
 * NOTE: The bookings table only stores the current status. This table
 *       stores every status transition, enabling full dispute resolution.
 *       Immutable by design.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('booking_history', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('from_status', ['active', 'cancelled', 'attended', 'no_show'])->nullable();
            $table->enum('to_status', ['active', 'cancelled', 'attended', 'no_show']);
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->string('reason', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('booking_history');
    }
};

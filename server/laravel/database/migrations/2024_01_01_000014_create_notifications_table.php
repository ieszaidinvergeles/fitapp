<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the notifications table for application-level broadcast notifications.
 *
 * SRP: Solely responsible for the notifications schema lifecycle.
 * NOTE: This table stores custom gym notifications, distinct from
 *       Laravel's built-in notification system.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable();
            $table->string('title', 200);
            $table->text('body')->nullable();
            $table->enum('target_audience', ['global', 'staff_only', 'specific_gym', 'specific_user'])->nullable();
            $table->foreignId('related_gym_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('related_gym_id')->references('id')->on('gyms')->nullOnDelete();

        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the users table.
 *
 * SRP: Solely responsible for the users schema lifecycle.
 * NOTE: Remove the default Laravel users migration (2014_10_12_000000)
 *       and the password_reset_tokens migration before running this.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', length: 20)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('password_hash')->nullable();
            $table->enum('role', ['admin', 'manager', 'staff', 'client', 'user_online'])->nullable();
            $table->string('full_name', length: 300)->nullable();
            $table->string('dni', length: 9)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('profile_photo_url')->nullable();
            $table->foreignId('current_gym_id')->nullable();     // ->constrained('gyms')->nullOnDelete();
            $table->foreignId('membership_plan_id')->nullable(); // ->constrained('membership_plans')->nullOnDelete();
            $table->string('membership_status')->nullable()->comment('active, paused, expired');
                // convertir a array (enum)
            $table->integer('cancellation_strikes')->default(0)->comment('Count of late cancellations');
            $table->boolean('is_blocked_from_booking')->default(false);
            $table->timestamps();

            $table->foreign('current_gym_id')->references('id')->on('gyms');
            $table->foreign('membership_plan_id')->references('id')->on('membership_plans');

        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

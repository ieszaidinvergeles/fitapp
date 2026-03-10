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
            $table->string('username', 20);
            $table->string('email', 160)->unique();
            $table->string('password_hash', 255)->nullable();
            $table->enum('role', ['admin', 'manager', 'staff', 'client', 'user_online']);
            $table->string('full_name', 160)->nullable(); 
            $table->string('dni', 9);
            $table->date('birth_date');
            $table->string('profile_photo_url', 600)->nullable();

            $table->foreignId('current_gym_id')->nullable();
            $table->foreignId('membership_plan_id')->nullable();

            $table->enum('membership_status', ['active', 'paused', 'expired']);
            $table->integer('cancellation_strikes')->default(0);
            $table->boolean('is_blocked_from_booking')->default(false);
            $table->timestamps();

            $table->foreign('current_gym_id')->references('id')->on('gyms')->onDelete('set null');
            $table->foreign('membership_plan_id')->references('id')->on('membership_plans')->onDelete('set null');
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

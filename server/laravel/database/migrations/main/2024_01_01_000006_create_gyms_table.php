<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the gyms table.
 *
 * SRP: Solely responsible for the gyms schema lifecycle.
 * NOTE: gyms and users have a circular FK dependency — gyms.manager_id
 *       references users, and users.current_gym_id references gyms.
 *       Laravel migrations execute one by one in order, so the FK cannot
 *       be declared here because users does not exist yet at this point.
 *       manager_id is created as a plain unsignedBigInteger (same column
 *       type as foreignId) and the FK constraint is added in migration
 *       000008 once users has been created.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('gyms', function (Blueprint $table) {
            $table->id();
            $table->string('name',80);
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('address', 160);
            $table->string('city', 80);
            $table->string('location_coords', 100)->nullable();
            $table->string('phone', 20);
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('gyms');
    }
};

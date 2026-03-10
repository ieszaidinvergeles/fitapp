<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the gyms table without the manager_id FK.
 *
 * SRP: Solely responsible for the gyms schema lifecycle.
 * NOTE: The manager_id foreign key is added in migration 000008
 *       after the users table exists, breaking the circular dependency.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('gyms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            // es lo mismo que hacer la referenciación de a una clave foranea
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('location_coords')->nullable();
            $table->string('phone')->nullable();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('gyms');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the gym_inventory pivot table.
 *
 * SRP: Solely responsible for the gym_inventory schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('gym_inventory', function (Blueprint $table) {
            $table->foreignId('gym_id');       //->constrained('gyms')->cascadeOnDelete();
            $table->foreignId('equipment_id'); //->constrained('equipment')->cascadeOnDelete();
            $table->integer('quantity')->nullable();
            $table->string('status')->nullable();
            $table->primary(['gym_id', 'equipment_id']);

            $table->foreign('gym_id')->references('id')->on('gyms');
            $table->foreign('equipment_id')->references('id')->on('equipment');
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('gym_inventory');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the rooms table.
 *
 * SRP: Solely responsible for the rooms schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->nullable(); // ->constrained('gyms')->cascadeOnDelete();
            $table->string('name', length: 80)->nullable();
            $table->integer('capacity', 3)->nullable();

            $table->foreign('gym_id')->references('id')->on('gyms');

        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the activities table.
 *
 * SRP: Solely responsible for the activities schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->text('description')->nullable();
            $table->enum('intensity_level', ['low', 'medium', 'high', 'extreme']);
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};

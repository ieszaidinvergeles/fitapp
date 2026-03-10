<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the equipment table.
 *
 * SRP: Solely responsible for the equipment schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name',80);
            $table->text('description')->nullable();
            $table->boolean('is_home_accessible')->default(false);
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the diet_plans table.
 *
 * SRP: Solely responsible for the diet_plans schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->text('goal_description')->nullable();
            $table->string('cover_image_url', 500)->nullable();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('diet_plans');
    }
};

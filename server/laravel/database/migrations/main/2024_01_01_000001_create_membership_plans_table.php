<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the membership_plans table.
 *
 * SRP: Solely responsible for the membership_plans schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->enum('type', ['physical', 'online', 'duo']);
            $table->boolean('allow_partner_link')->default(false);
            $table->decimal('price', 6, 2);
            $table->string('badge_image_url', 500)->nullable();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};

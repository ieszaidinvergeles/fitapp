<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the user_favorites polymorphic-style table.
 *
 * SRP: Solely responsible for the user_favorites schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->foreignId('user_id');
            $table->enum('entity_type', ['gym', 'activity', 'routine']);
            $table->unsignedBigInteger('entity_id');
            $table->primary(['user_id', 'entity_type', 'entity_id']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};

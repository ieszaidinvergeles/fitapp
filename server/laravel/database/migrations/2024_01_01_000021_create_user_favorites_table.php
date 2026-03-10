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
            $table->foreignId('user_id'); // ->constrained('users')->cascadeOnDelete();
            $table->string('entity_type')->comment('gym, activity, routine');
                // array (enum)
            $table->unsignedBigInteger('entity_id');
            $table->primary(['user_id', 'entity_type', 'entity_id']);

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};

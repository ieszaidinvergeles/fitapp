<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the user_partners table.
 *
 * SRP: Solely responsible for the user_partners schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('user_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_user_id'); // ->constrained('users')->cascadeOnDelete();
            $table->foreignId('partner_user_id'); // ->constrained('users')->cascadeOnDelete();
            $table->timestamp('linked_at')->useCurrent();

            $table->foreign('primary_user_id')->references('id')->on('users');
            $table->foreign('partner_user_id')->references('id')->on('users');

        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('user_partners');
    }
};

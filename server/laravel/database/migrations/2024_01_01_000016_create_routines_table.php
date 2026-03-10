<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the routines table.
 *
 * SRP: Solely responsible for the routines schema lifecycle.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('routines', function (Blueprint $table) {
            $table->id();
            $table->string('name', length:80)->nullable();
            $table->text('description', 280)->nullable();
            $table->foreignId('creator_id')->nullable();              // ->constrained('users')->nullOnDelete();
            $table->string('difficulty_level')->nullable();
                // cambiar a array
            $table->integer('estimated_duration_min', 5)->nullable();
            $table->foreignId('associated_diet_plan_id')->nullable(); // ->constrained('diet_plans')->nullOnDelete()
                //   ->comment('Optional link');

            $table->foreign('creator_id')->references('id')->on('users');
            $table->foreign('associated_diet_plan_id')->references('id')->on('diet_plans');

        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('routines');
    }
};

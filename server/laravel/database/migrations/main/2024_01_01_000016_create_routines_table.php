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
            $table->string('name', 80);
            $table->text('description');
            $table->foreignId('creator_id')->nullable(); 
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced', 'expert']);
            $table->integer('estimated_duration_min');
            $table->foreignId('associated_diet_plan_id')->nullable(); 

            $table->foreign('creator_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('associated_diet_plan_id')->references('id')->on('diet_plans')->nullOnDelete();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('routines');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the audit_logs table.
 *
 * SRP: Solely responsible for defining the audit log schema.
 * NOTE: No FK constraints — this table stores snapshots of data
 *       that may be deleted from the main DB. Immutable by design:
 *       no UPDATE or DELETE operations should ever be performed.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_role', 20)->nullable();
            $table->enum('action', ['created', 'updated', 'deleted']);
            $table->string('entity_type', 60);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the admin_action_logs table.
 *
 * SRP: Solely responsible for defining the privileged action log schema.
 * NOTE: Records every sensitive action performed by admin or manager roles.
 *       The action field is a free varchar to allow new actions without
 *       requiring a migration. Immutable by design.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('admin_action_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_role', 20);
            $table->string('action', 80);
            $table->string('target_entity_type', 60)->nullable();
            $table->unsignedBigInteger('target_entity_id')->nullable();
            $table->json('payload')->nullable();
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('admin_action_logs');
    }
};

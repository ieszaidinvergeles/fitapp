<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the notification_delivery_logs table.
 *
 * SRP: Solely responsible for defining the notification delivery log schema.
 * NOTE: The notifications table stores the notification itself. This table
 *       tracks individual delivery status per recipient and channel.
 *       Immutable by design.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('notification_delivery_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->unsignedBigInteger('recipient_id');
            $table->enum('channel', ['push', 'email', 'in_app']);
            $table->enum('status', ['sent', 'delivered', 'read', 'failed']);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('notification_delivery_logs');
    }
};

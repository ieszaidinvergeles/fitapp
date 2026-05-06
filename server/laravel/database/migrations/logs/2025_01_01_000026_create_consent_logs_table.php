<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the consent_logs table.
 *
 * SRP: Solely responsible for defining the GDPR consent log schema.
 * NOTE: This is the most legally critical log table. Every consent
 *       grant or revocation must be recorded with full context.
 *       Immutable by design — required for GDPR compliance evidence.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('consent_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('consent_type', [
                'terms_of_service',
                'privacy_policy',
                'marketing_emails',
                'share_body_metrics',
                'share_workout_stats',
                'share_attendance',
                'data_processing',
            ]);
            $table->enum('action', ['granted', 'revoked']);
            $table->string('version', 20);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};

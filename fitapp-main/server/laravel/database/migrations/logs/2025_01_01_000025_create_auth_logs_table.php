<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the auth_logs table.
 *
 * SRP: Solely responsible for defining the authentication log schema.
 * NOTE: No FK on user_id — the user may not exist if login failed
 *       with an unknown email. Immutable by design.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::create('auth_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email_attempt', 160);
            $table->enum('event', [
                'login_ok',
                'login_failed',
                'logout',
                'password_reset_requested',
                'password_reset_ok',
                'token_refreshed',
                'account_blocked',
            ]);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::dropIfExists('auth_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the email_verified_at column to the users table.
 *
 * SRP: Solely responsible for adding the single column required by
 *      Laravel's MustVerifyEmail contract, which was absent from the
 *      original immutable schema.
 *
 * NOTE: The base schema is immutable as of 2026-03-10. This migration
 *       is additive only — no existing column is modified.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('email_verified_at');
        });
    }
};

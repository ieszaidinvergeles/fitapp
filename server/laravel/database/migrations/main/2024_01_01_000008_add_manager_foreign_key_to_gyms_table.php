<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves the circular FK dependency between gyms and users.
 *
 * SRP: Solely responsible for adding the manager_id FK constraint to gyms.
 * OCP: Extends the gyms schema without modifying migration 000006.
 * NOTE: This migration runs after 000007 (users), so users already exists
 *       and the foreign key can be safely declared at this point.
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });
    }
};

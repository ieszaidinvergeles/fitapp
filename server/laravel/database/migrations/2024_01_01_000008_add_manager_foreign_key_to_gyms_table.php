<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the manager_id foreign key to gyms now that users exists.
 *
 * SRP: Solely responsible for adding the circular FK that resolves
 *      the gyms <-> users dependency.
 * OCP: Extends the gyms schema without modifying the original migration.
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

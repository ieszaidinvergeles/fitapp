<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE users
            MODIFY role ENUM('admin', 'manager', 'assistant', 'staff', 'client', 'user_online') NOT NULL
        ");
    }

    /** @inheritdoc */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE users
            MODIFY role ENUM('admin', 'manager', 'staff', 'client', 'user_online') NOT NULL
        ");
    }
};

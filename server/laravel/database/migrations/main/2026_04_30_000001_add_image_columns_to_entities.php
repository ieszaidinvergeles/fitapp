<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds image-related columns to the entities that require them.
 *
 * SRP: Solely responsible for the schema changes related to the image
 *      management system introduced in v0.15.0.
 *
 * Note: users.profile_photo_url, exercises.image_url, and recipes.image_url
 *       already exist from earlier migrations and are NOT touched here.
 *       The column type remains VARCHAR(500) for consistency.
 *
 * Note: equipment and activities had their image_url exposed in their
 *       API Resources before this migration, but the actual database column
 *       was missing. This migration resolves that inconsistency.
 *
 * Note: Each column addition is guarded with hasColumn() so that this
 *       migration is idempotent — it can be run safely on both fresh
 *       installs (where CREATE migrations already added the columns) and
 *       on existing databases (where the columns do not yet exist).
 */
return new class extends Migration
{
    /** @inheritdoc */
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment', 'image_url')) {
                $table->string('image_url', 500)->nullable()->after('is_home_accessible');
            }
        });

        Schema::table('gyms', function (Blueprint $table) {
            if (!Schema::hasColumn('gyms', 'logo_url')) {
                $table->string('logo_url', 500)->nullable()->after('phone');
            }
        });

        Schema::table('routines', function (Blueprint $table) {
            if (!Schema::hasColumn('routines', 'cover_image_url')) {
                $table->string('cover_image_url', 500)->nullable()->after('estimated_duration_min');
            }
        });

        Schema::table('diet_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('diet_plans', 'cover_image_url')) {
                $table->string('cover_image_url', 500)->nullable()->after('goal_description');
            }
        });

        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'image_url')) {
                $table->string('image_url', 500)->nullable()->after('capacity');
            }
        });

        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'image_url')) {
                $table->string('image_url', 500)->nullable()->after('intensity_level');
            }
        });

        Schema::table('membership_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('membership_plans', 'badge_image_url')) {
                $table->string('badge_image_url', 500)->nullable()->after('price');
            }
        });
    }

    /** @inheritdoc */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            if (Schema::hasColumn('equipment', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });

        Schema::table('gyms', function (Blueprint $table) {
            if (Schema::hasColumn('gyms', 'logo_url')) {
                $table->dropColumn('logo_url');
            }
        });

        Schema::table('routines', function (Blueprint $table) {
            if (Schema::hasColumn('routines', 'cover_image_url')) {
                $table->dropColumn('cover_image_url');
            }
        });

        Schema::table('diet_plans', function (Blueprint $table) {
            if (Schema::hasColumn('diet_plans', 'cover_image_url')) {
                $table->dropColumn('cover_image_url');
            }
        });

        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });

        Schema::table('activities', function (Blueprint $table) {
            if (Schema::hasColumn('activities', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });

        Schema::table('membership_plans', function (Blueprint $table) {
            if (Schema::hasColumn('membership_plans', 'badge_image_url')) {
                $table->dropColumn('badge_image_url');
            }
        });
    }
};

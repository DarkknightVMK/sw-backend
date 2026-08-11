<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ensures avatar_spaces.user_id exists — the space OWNER column.
 *
 * The whole app treats `avatar_spaces.user_id` as the owner (e.g.
 * SpaceController::mySpaces, the space service layer, and the space-settings
 * editor's ownership check), but no committed migration ever created it — it
 * was added by hand on the live DB. That means a fresh `php artisan migrate`
 * builds avatar_spaces WITHOUT user_id, so ownership resolves to NULL and the
 * owner-only settings editor never unlocks.
 *
 * This migration adds the column only when it's missing (hasColumn guard), so
 * it's a no-op on a production DB that already has it and a fix on any fresh
 * deploy. Nullable + no FK on purpose: existing rows predate it, and the
 * avatars/users data on legacy installs is too messy to safely constrain.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('avatar_spaces', 'user_id')) {
            Schema::table('avatar_spaces', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->after('avatar_id');
            });
        }
    }

    public function down(): void
    {
        // Intentionally does NOT drop user_id: on production the column predates
        // this migration and is load-bearing for the rest of the app. Rolling
        // this migration back must not delete owner data.
    }
};

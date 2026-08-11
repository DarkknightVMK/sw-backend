<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Space settings: theme + decor.
 *
 * The space-settings editor lets an owner pick a visual "theme" and two
 * decor surfaces (wallpaper + flooring). None of these existed on
 * avatar_spaces before — they're stored as short preset keys the client
 * resolves to real assets, so a NULL means "use the space model's default".
 * Kept as plain nullable strings (not FKs) so presets can be added/removed
 * client-side without a schema change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avatar_spaces', function (Blueprint $table) {
            if (!Schema::hasColumn('avatar_spaces', 'theme')) {
                $table->string('theme', 64)->nullable()->after('desc');
            }
            if (!Schema::hasColumn('avatar_spaces', 'wallpaper')) {
                $table->string('wallpaper', 64)->nullable()->after('theme');
            }
            if (!Schema::hasColumn('avatar_spaces', 'flooring')) {
                $table->string('flooring', 64)->nullable()->after('wallpaper');
            }
        });
    }

    public function down(): void
    {
        Schema::table('avatar_spaces', function (Blueprint $table) {
            foreach (['theme', 'wallpaper', 'flooring'] as $col) {
                if (Schema::hasColumn('avatar_spaces', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

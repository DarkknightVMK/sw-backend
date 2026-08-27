<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePvpTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pvp_match_configs')) {
            Schema::create('pvp_match_configs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('space_id')->unique();
                $table->boolean('enabled')->default(false);
                $table->text('config');
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('pvp_match_configs', 'enabled')) {
            Schema::table('pvp_match_configs', fn (Blueprint $table) => $table->boolean('enabled')->default(false)->after('space_id'));
        }

        if (!Schema::hasTable('pvp_matches')) {
            Schema::create('pvp_matches', function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('space_id')->index();
                $table->string('genre')->default('spacejump'); $table->string('status')->default('lobby');
                $table->string('mode')->default('teams'); $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable(); $table->string('winner')->nullable();
                $table->text('last_event')->nullable(); $table->timestamp('finalized_at')->nullable(); $table->timestamps();
            });
        }
        if (Schema::hasTable('pvp_matches') && !Schema::hasColumn('pvp_matches', 'last_event')) {
            Schema::table('pvp_matches', fn (Blueprint $table) => $table->text('last_event')->nullable()->after('winner'));
        }
        if (Schema::hasTable('pvp_matches') && !Schema::hasColumn('pvp_matches', 'finalized_at')) {
            Schema::table('pvp_matches', fn (Blueprint $table) => $table->timestamp('finalized_at')->nullable()->after('last_event'));
        }
        if (!Schema::hasTable('pvp_match_players')) {
            Schema::create('pvp_match_players', function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('match_id')->index(); $table->string('avatar_id')->index();
                $table->string('team')->nullable(); $table->string('state')->default('queued');
                $table->integer('health')->default(100);
                $table->integer('score')->default(0); $table->integer('kos')->default(0);
                $table->integer('spawn_index')->nullable(); $table->timestamp('eliminated_at')->nullable();
                $table->timestamps(); $table->unique(['match_id', 'avatar_id']);
            });
        }
        if (Schema::hasTable('pvp_match_players') && !Schema::hasColumn('pvp_match_players', 'health')) {
            Schema::table('pvp_match_players', fn (Blueprint $table) => $table->integer('health')->default(100)->after('state'));
        }
        DB::table('pvp_match_configs')->whereIn('space_id', [9999, 10031])->update(['enabled' => true]);
    }

    public function down()
    {
        Schema::dropIfExists('pvp_match_players');
        Schema::dropIfExists('pvp_matches');
        Schema::dropIfExists('pvp_match_configs');
    }
}

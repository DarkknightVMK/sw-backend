<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addtoassnapshot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avatar_spaces', function (Blueprint $table) {
            $table->string('spaceSnapShotSource')->nullable();
            $table->string('spaceThumbnailSource')->nullable();
            $table->string('spaceGroupName')->nullable();
            $table->string('spaceGroupId')->nullable();
            $table->string('spaceJoinChannel')->nullable();
            $table->boolean('spaceShowInSearch')->default(true);
            $table->boolean('spaceShowAds')->default(false);
            $table->string('type')->default('A');
            $table->integer('rating')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avatar_spaces', function (Blueprint $table) {
            //
        });
    }
}

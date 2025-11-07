<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AvatarMissionKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avatar_mission_keys', function (Blueprint $table) {
            // $table->id();
            $table->integer('avatar_id')->unsigned();
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->string('missionkey_key');
            $table->string('missionkey_visible')->default('N');
            $table->bigInteger('missionkey_mission_id')->unsigned();
            $table->timestamp('missionkey_expires');
            $table->foreign('missionkey_mission_id')->references('id')->on('mission_tasks');
            // $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avatar_mission_keys');
    }
}

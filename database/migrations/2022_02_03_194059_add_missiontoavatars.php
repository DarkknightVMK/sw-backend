<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissiontoavatars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avatar_missions', function (Blueprint $table) {
            //
            // $table->id();
            $table->integer('avatar_id')->unsigned();
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->bigInteger('pickupSpaceId')->unsigned()->nullable();
            $table->foreign('pickupSpaceId')->references('id')->on('avatar_spaces');
            $table->date('activated')->nullable(); //avatarmission_activated
            $table->boolean('isTesting')->default(false); // avatarmission_is_test
            $table->bigInteger('missionId')->unsigned()->nullable();
            $table->foreign('missionId')->references('id')->on('missions');
            $table->string('pickupLocation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avatar_missions');
    }
}

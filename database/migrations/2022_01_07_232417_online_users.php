<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OnlineUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onlineUsers', function (Blueprint $table) 
        {
            $table->bigInteger('space_id')->primary()->unsigned();
            $table->foreign('space_id')->references('id')->on('avatar_spaces')->onDelete('cascade');
            $table->integer('avatar_id')->unsigned();
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            // add more later
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
        Schema::dropIfExists('onlineUsers');
    }
}

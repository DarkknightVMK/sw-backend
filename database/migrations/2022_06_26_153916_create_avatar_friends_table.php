<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvatarFriendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('avatar_friend_requests');

        Schema::create('avatar_friends', function (Blueprint $table) {
            $table->id();
            $table->string('avatar_id');
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->string('friend_id');
            $table->foreign('friend_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->boolean('reciprocal')->default(false);
            $table->timestamps();
        });

        Schema::table('onlineUsers', function (Blueprint $table) {
            $table->boolean('online')->default(false);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avatar_friends');
    }
}

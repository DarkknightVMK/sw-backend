<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('avatar_id', 32);
            $table->string('told', 32);
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->foreign('told')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->string('fromAvatarId',32);
            $table->foreign('fromAvatarId')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->string('status', 1)->default('N'); // R = Read, N = unread
            $table->string('type'); // S = System, A = avatar, M = Mod, U = user
            $table->string('subject');
            $table->longText('text');
            $table->timestamp('timeRead')->nullable();
            $table->string('ref', 20); // random string
            $table->string('fromEntity', 1)->nullable()->default('A'); // A = Avatar, U = User
            $table->string('toEntity', 1)->nullable()->default('A'); // A = Avatar, U = User
            $table->boolean('activeInbox')->nullable()->default(true);
            $table->boolean('activeSent')->nullable()->default(true);
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
        Schema::dropIfExists('messages');
    }
}

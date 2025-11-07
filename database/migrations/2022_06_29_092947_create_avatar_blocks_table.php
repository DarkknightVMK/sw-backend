<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvatarBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avatar_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('avatar_id');
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->string('block_id');
            $table->foreign('block_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
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
        Schema::dropIfExists('avatar_blocks');
    }
}

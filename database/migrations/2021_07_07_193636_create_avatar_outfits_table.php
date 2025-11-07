<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvatarOutfitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avatar_outfits', function (Blueprint $table) 
        {
            $table->string('outfit_id')->primary()->unique();
            $table->integer('avatar_id')->unsigned();
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars');
            $table->string('outfit_config')->default("");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avatar_outfits');
    }
}

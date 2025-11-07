<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AvatarItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avatar_items', function (Blueprint $table) {
            
            $table->string('item_id')->primary()->unique();
            $table->integer('avatar_id')->unsigned();
            $table->unsignedInteger('model_id');
            $table->integer('item_count')->nullable();
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars');
            $table->foreign('model_id')->references('model_id')->on('items');
            $table->string('item_icon_postfix')->nullable();
            $table->string('item_config')->default("");
            $table->string('giftWrapModel_source')->nullable();
            $table->string('giftWrapModel_icon')->nullable();
            $table->integer('item_gifted_by_avatar')->nullable();
            $table->boolean('item_purchased_with_tokens')->nullable();
            $table->integer('item_last_purchased_tokens')->nullable();
            $table->integer('item_last_purchased_amount')->nullable();

            //Spaces
            $table->bigInteger('space_id')->unsigned()->nullable();
            $table->foreign('space_id')->references('id')->on('avatar_spaces');
            $table->integer('space_angle')->nullable();
            $table->string('space_bounds')->nullable();
            $table->integer('space_x')->nullable();
            $table->integer('space_y')->nullable();
            $table->integer('space_z')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avatar_items');
    }
}

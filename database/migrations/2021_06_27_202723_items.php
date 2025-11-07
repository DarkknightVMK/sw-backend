<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Items extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) 
        {
            $table->unsignedInteger('model_id')->primary()->unique();
            $table->string('model_cid')->default('com.smallworlds.entity.item.SpriteItem');
            $table->string('model_icon')->nullable();
            $table->string('model_tags');
            $table->string('model_source');
            $table->string('model_desc');
            $table->string('model_details');
            $table->boolean('model_premium_only')->default(false);
            $table->integer('model_price_gold')->nullable();
            $table->integer('model_price_tokens')->nullable();
            $table->integer('model_allowedCount')->nullable();
            $table->boolean('persistedConfig')->default(false);
            $table->integer('model_min_xplevel')->default(1);

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
        Schema::dropIfExists('items');
        

    }
}

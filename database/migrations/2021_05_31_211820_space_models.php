<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SpaceModels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('space_models', function (Blueprint $table)
        {

            $table->integer('model_min_cl')->default(1);
            $table->string('model_cid')->default('com.smallworlds.entity.space.Space');
            $table->string('model_icon')->nullable();
            $table->string('model_tags')->nullable();
            $table->string('model_xplevelscript_id')->nullable();
            $table->string('model_details');
            $table->string('model_default_edit_control')->default('C');
            $table->string('model_category')->default('B');
            $table->string('model_source');
            $table->string('model_default_access_control')->default('A');
            $table->string('model_desc');
            $table->string('model_game_id')->nullable();
            $table->unsignedInteger('model_id')->primary()->unique();
            $table->string('model_premium_only')->default('N');
            $table->integer('model_price_tokens')->default(0);
            $table->string('model_type')->default('space');
            $table->string('model_xpleveltype_id')->nullable();
            $table->string('model_default_move_control')->default('C');
            $table->string('model_xml')->nullable();
            $table->integer('model_allowed_count')->default(0);
            $table->integer('model_price')->nullable();
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
        Schema::dropIfExists('space_models');
        Schema::dropIfExists('space_Models');

    }
}

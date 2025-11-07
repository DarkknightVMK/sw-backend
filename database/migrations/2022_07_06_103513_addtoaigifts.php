<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addtoaigifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avatar_items', function (Blueprint $table) {
            //
            //giftOpenDate Jul 06 2022
            $table->date('giftOpenDate')->nullable();
            // update a column giftWrapModel_source to giftWrapModel_id
            $table->integer('giftWrapModel_id')->unsigned()->nullable();
            // ref model_id to giftWrapModel_id
            $table->foreign('giftWrapModel_id')->references('model_id')->on('items')->onDelete('cascade');
            $table->foreign('item_gifted_by_avatar')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->string('giftMessage')->nullable();
            $table->string('giftMessageUnwrap')->nullable();

            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avatar_items', function (Blueprint $table) {
            //
        });
    }
}

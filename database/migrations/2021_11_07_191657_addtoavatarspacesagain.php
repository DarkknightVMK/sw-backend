<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addtoavatarspacesagain extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avatar_spaces', function (Blueprint $table) {
            $table->boolean('isShowroom')->default(false);
            $table->boolean('active')->default(true);
            $table->boolean('indexed')->default(true);
            $table->integer('shopDisplayOrder')->default(0);
            $table->integer('maxInstances')->default(0);
            $table->integer('featuredIndex')->default(0);
            $table->string('forSale')->default('N');
            $table->integer('salePriceTokens')->default(0);
            $table->integer('salePriceGold')->default(0);
            $table->string('alias')->unique()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avatar_spaces', function (Blueprint $table) {
            //
        });
    }
}

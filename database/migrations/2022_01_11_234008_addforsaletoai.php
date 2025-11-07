<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addforsaletoai extends Migration
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
            $table->string('forSale')->default('N');
            $table->integer('price')->nullable();
            $table->integer('priceTokens')->nullable();
            $table->integer('lastPurchasedTokens')->nullable();
            $table->string('iconPostfix')->nullable();
            $table->string('thumbPostfix')->nullable();
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

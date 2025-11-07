<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddtoaitemsSecurity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avatar_items', function (Blueprint $table) 
        {
            $table->string('access')->default('A');
            $table->string('edit')->default('C');
            $table->string('move')->default('C');
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Actionbar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actionbar', function (Blueprint $table) 
        {
            $table->unsignedInteger('actionbaritem_id')->autoIncrement()->unique();
            $table->integer('actionbaritem_column');
            $table->string('actionbaritem_name'); //item Id
            $table->integer('actionbaritem_row');
            $table->string('actionbaritem_type');
            $table->string('actionbaritem_cid');
            $table->string('actionbaritem_label')->nullable();
            $table->string('actionbaritem_url');
            $table->integer('actionbaritem_user_id')->unsigned();
            $table->foreign('actionbaritem_user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('actionbar');
    }
}

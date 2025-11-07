<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileDataAvatar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avatar_profile_data', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->foreign('id')->references('id')->on('profile_datafield')->onDelete('cascade');
            $table->string('optionDescs');
            $table->foreign('optionDescs')->references('desc')->on('profile_datafield_option')->onDelete('cascade');
            $table->string('color');
            $table->foreign('color')->references('color')->on('profile_datafield_option')->onDelete('cascade');
            $table->string('iconUrl');
            $table->foreign('iconUrl')->references('defaultIconUrl')->on('profile_datafield')->onDelete('cascade');
            $table->bigInteger('optionIds')->unsigned();
            $table->foreign('optionIds')->references('id')->on('profile_datafield_option')->onDelete('cascade');
            $table->unsignedInteger('avatar_id');
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
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
        //
    }
}

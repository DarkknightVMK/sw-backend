<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//         Schema::table('space_favorites', function (Blueprint $table) 
// {
//     // $table->id();
//     $table->timestamps();
// });

        // Schema::create('avatar_friend_requests', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedInteger('avatar_id');
        //     $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
        //     $table->unsignedInteger('friend_id');
        //     $table->foreign('friend_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            
        //     $table->timestamps();
        // });

        // Schema::create('xp', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedInteger('xp')->default(0);
        //     $table->unsignedInteger('level')->default(1);
        //     $table->unsignedInteger('xpleveltype');
        //     $table->foreign('xpleveltype')->references('id')->on('xp_type')->onDelete('cascade');
        //     $table->unsignedInteger('avatar_id');
        //     $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
        //     $table->timestamps();
        // });

        // Schema::create('profile_datafield_option', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('desc');
        //     $table->string('color');
        //     $table->string('iconUrl');
        //     $table->unsignedInteger('datafield_id');
        //     $table->foreign('datafield_id')->references('id')->on('profile_datafield')->onDelete('cascade');
        //     $table->timestamps();
        // });

        // Schema::create('avatar_profile_dataText', function (Blueprint $table) {
        //     $table->bigInteger('id')->unsigned();
        //     $table->foreign('id')->references('id')->on('profile_datatext')->onDelete('cascade');
        //     $table->longText('value');
        //     $table->unsignedInteger('avatar_id');
        //     $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
        //     $table->timestamps();
        // });

        // Schema::table('avatar_items', function (Blueprint $table) {
        //     $table->string('baseItemId');
        //     $table->foreign('baseItemId')->references('baseItemId')->on('items')->onDelete('cascade');
        // });

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

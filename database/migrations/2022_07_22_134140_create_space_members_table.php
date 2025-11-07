<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpaceMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('space_members', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('space_id')->unsigned();
            $table->foreign('space_id')->references('id')->on('avatar_spaces')->onDelete('cascade');
            $table->bigInteger('spacerole_id')->unsigned();
            $table->foreign('spacerole_id')->references('id')->on('space_roles')->onDelete('cascade');
            $table->string('avatar_id');
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
        Schema::dropIfExists('space_members');
    }
}

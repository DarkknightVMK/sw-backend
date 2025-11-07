<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpaceBansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('space_bans', function (Blueprint $table) {
            $table->id();
            $table->string('avatar_id')->length(32)->nullable();
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->bigInteger('space_id')->unsigned();
            $table->foreign('space_id')->references('id')->on('avatar_spaces')->onDelete('cascade');
            $table->string('reason')->nullable();
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
        Schema::dropIfExists('space_bans');
    }
}

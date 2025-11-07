<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AvatarSpaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avatar_spaces', function (Blueprint $table) {
            $table->id();
            $table->integer('avatar_id')->unsigned();
            $table->unsignedInteger('modelId');
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars');
            $table->foreign('modelId')->references('model_id')->on('space_models');

            $table->string('name');
            $table->string('desc')->nullable();
            $table->string('accessControl')->default('O');
            $table->integer('currentVisitors')->nullable()->default(0);
            $table->string('config')->nullable();
            $table->boolean('showInPlacePanel')->nullable()->default(true);
            $table->boolean('instanceable')->nullable()->default(false);
            $table->integer('maxVisitors')->nullable()->default(20);
            $table->string('password', 100)->nullable();
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
        Schema::dropIfExists('avatar_spaces');

    }
}

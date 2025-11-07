<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvatarArtifactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avatar_artifacts', function (Blueprint $table) {
            // $table->id();
            $table->integer('avatar_id')->unsigned();
            $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->string('artifact_key');
            $table->string('artifact_visible')->default('Y');
            $table->timestamp('artifact_expires');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avatar_artifacts');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('motto')->nullable();
            $table->longText('configString');
            $table->longText('memoryString')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('ownerId')->unsigned();
            $table->foreign('ownerId')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->string('snapshotUrl');
            $table->string('headUrl');
            $table->string('thumbUrl');
            $table->string('snapshotPostfix');
            $table->string('headPostfix');
            $table->string('thumbPostfix');
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
        Schema::dropIfExists('pets');
    }
}

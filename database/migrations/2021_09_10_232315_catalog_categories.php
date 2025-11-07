<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CatalogCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogCategories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active');
            $table->boolean('isManuallyControlled');
            $table->boolean('isControlledByPopularity');
            $table->string('type');
            $table->string('iconUrl')->nullable();
            $table->bigInteger('parentId')->unsigned()->nullable();
            $table->foreign('parentId')->references('id')->on('catalogCategories');
            $table->string('parentName')->nullable();
            $table->longText('children')->nullable();
            $table->integer('orderIndex');
            $table->boolean('sortsByOrderIndex');
            $table->boolean('sortsAlphabetically');
            $table->boolean('hasSeparator');
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
        Schema::dropIfExists('catalogCategories');
    }
}

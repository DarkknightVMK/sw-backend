<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogItemEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalog_item_entries', function (Blueprint $table) {
            $table->id();
            //int id
            //string parentCategoryId
            $table->bigInteger('parentCategoryId')->unsigned()->nullable();
            $table->foreign('parentCategoryId')->references('parentId')->on('catalog_categories');

            //parentCategoryName
            $table->string('parentCategoryName')->nullable();
            $table->foreign('parentCategoryName')->references('parentName')->on('catalog_categories');
            //int categoryId
            $table->bigInteger('categoryId')->unsigned();
            $table->foreign('categoryId')->references('id')->on('catalog_categories');
            //string categoryName
            $table->string('categoryName')->nullable();
            $table->foreign('categoryName')->references('name')->on('catalog_categories');
            //int orderIndex
            $table->integer('orderIndex')->nullable();
            //int spaceId
            $table->integer('modelId')->unsigned();
            $table->foreign('modelId')->references('model_id')->on('items');
            //bool spaceBlocked
            $table->boolean('discounted')->nullable();
        
            //bool active
            $table->boolean('active')->nullable();
            //date startDate
            $table->date('startDate')->nullable();
            //date endDate
            $table->date('endDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog_item_entries');
    }
}

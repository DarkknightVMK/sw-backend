<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class S2w extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s2w_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('background', 15, 1)->nullable();
            $table->double('weighting', 15, 2)->nullable()->default(0);
            $table->double('count', 15, 1)->nullable()->default(0);
            $table->double('order', 15, 1)->nullable();
            $table->boolean('isDeluxe')->nullable()->default(false);
            $table->timestamps();
        });
        Schema::create('s2w_prizes', function (Blueprint $table) {
            $table->id();
            $table->string('tokens')->nullable();
            $table->string('gold')->nullable();
            $table->double('modelCount', 15, 1)->nullable()->default(1);
            $table->double('type', 15, 1)->nullable();
            $table->boolean('isInPreviewList')->nullable()->default(false);
            $table->boolean('isBraggable')->nullable()->default(false);
            // relation to category
            $table->unsignedBigInteger('categoryId');
            $table->foreign('categoryId')->references('id')->on('s2w_categories');
            // relation to model
            $table->unsignedBigInteger('modelId')->nullable();
            $table->foreign('modelId')->references('id')->on('items');
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
        Schema::dropIfExists('s2w_categories');
        Schema::dropIfExists('s2w_prizes');

    }
}

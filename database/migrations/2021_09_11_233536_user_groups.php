<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissionCategories', function (Blueprint $table)
        {
            $table->unsignedInteger('id')->autoIncrement()->unique();
            $table->string('name');
            $table->integer('product'); // User & SMI
            $table->integer('orderIndex');

        });

        Schema::create('userPermissions', function (Blueprint $table)
        {
            $table->unsignedInteger('id')->primary()->unique();
            $table->string('name');
            $table->string('desc');
            $table->integer('sortOrder')->nullable();
            $table->unsignedInteger('catId')->nullable();
            $table->foreign('catId')->references('id')->on('permissionCategories')->onDelete('cascade');

        });

        Schema::create('userGroups', function (Blueprint $table)
        {
            $table->id();
            $table->integer('uid')->unsigned();
            $table->foreign('uid')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('active');
            $table->boolean('indexed');
            $table->string('tags')->nullable();
            $table->string('website')->nullable();
            $table->string('accessControl')->nullable();
            $table->string('type');
            $table->unsignedInteger('permissionId')->nullable();
            $table->foreign('permissionId')->references('id')->on('userPermissions')->onDelete('cascade');
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
        Schema::dropIfExists('userGroups');
        Schema::dropIfExists('permissionCategories');
        Schema::dropIfExists('userPermissions');


    }
}

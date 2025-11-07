<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemainingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('xp_type', function (Blueprint $table) {
        //     $table->unsignedInteger('id')->primary()->unique();
        //     $table->string('desc');
        //     $table->unsignedInteger('dailyCap');
        //     $table->unsignedInteger('levelCap');
        //     $table->string('clientScriptUrl');
        //     $table->timestamps();
        // });
        
        
        // Schema::table('avatar_outfits', function (Blueprint $table) 
        // {
        //     $table->timestamps();
        // });

        // Schema::create('profile_datafield', function (Blueprint $table) {
        //     $table->unsignedInteger('id')->primary()->unique();
        //     $table->string('desc');
        //     $table->string('tooltipSelf');
        //     $table->string('tooltipOthers');
        //     $table->string('defaultIconUrl');
        //     $table->string('options');
        //     $table->timestamps();
        // });

        // Schema::create('profile_datatext', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('desc');
        //     $table->unsignedInteger('maxChars')->default(2000);
        // });

        

        
        

        // Schema::create('pets_tricks_prices', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('command');
        //     $table->unsignedInteger('price');
        //     $table->string('filename');
            
        // });

        
        // Schema::create('cp_ranks', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('canOverride');
        //     $table->string('themeName');
        //     $table->string('smallIconPath');
        //     $table->string('largeIconPath');
        //     $table->string('color');
        //     $table->unsignedInteger('minCitizenLevel');
        //     $table->timestamps();
        // });

        // Schema::table('avatars', function (Blueprint $table) 
        // {
        //     $table->string('selectedCPThemeKey');
        //     $table->string('overrideCPRankMinCL');
        //     $table->string('bonus');
        // });

        // Schema::table('items', function (Blueprint $table) {
        //     $table->string('baseItemId');    
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

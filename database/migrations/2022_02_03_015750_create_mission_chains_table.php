<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionChainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // REFERRED AS MISSION CHAINS
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(true);
            $table->boolean('indexed')->default(true);
            $table->boolean('expires')->default(true);
            $table->integer('featuredIndex')->nullable();
            $table->boolean('goodMission')->default(false);
            $table->boolean('followOnAutomatic')->default(true);
            $table->integer('activationGroup')->nullable(); // User can activate multiple missions at once? (staff purposes?)
            $table->integer('entryTokens');
            $table->string('title');
            $table->string('desc');
            $table->string('completedDesc');
            $table->integer('creatorAvatarId')->unsigned();
            $table->foreign('creatorAvatarId')->references('avatar_id')->on('avatars');
            $table->integer('creatorUserId')->unsigned()->nullable();
            $table->foreign('creatorUserId')->references('id')->on('users');
            //bonus
            $table->unsignedInteger('bonusModelId')->nullable();
            $table->foreign('bonusModelId')->references('model_id')->on('items');
            $table->integer('bonusGold')->nullable();
            $table->integer('bonusTokens')->nullable();
            //guaranteed
            $table->unsignedInteger('guaranteedModelId')->nullable();
            $table->foreign('guaranteedModelId')->references('model_id')->on('items');
            $table->integer('guaranteedGold')->nullable();
            $table->integer('guaranteedTokens')->nullable();
            $table->integer('guaranteedXP')->default(0);

            $table->bigInteger('lastActivatedSpaceId')->unsigned()->nullable();
            $table->foreign('lastActivatedSpaceId')->references('id')->on('avatar_spaces');

            //TASKS
            $table->bigInteger('firstTaskId')->unsigned()->nullable(); //missionchain_first_mission_id
            // $table->foreign('firstTaskId')->references('id')->on('mission_tasks');
            $table->bigInteger('lastTaskId')->unsigned()->nullable();
            // $table->foreign('lastTaskId')->references('id')->on('mission_tasks');

            $table->string('followOnMissionId')->nullable(); //missionchain_follow_on_missionchain_id
            // prob should ref id on this table

            $table->integer('plays')->default(0);
            $table->index('plays');
            $table->integer('totalPlays')->nullable();
            $table->foreign('totalPlays')->references('plays')->on('missions')->onDelete('cascade');
            $table->integer('totalPlayTime')->nullable();
            $table->integer('totalPlaysWeek')->nullable();
            $table->integer('totalPlaysMonth')->nullable();
            $table->integer('weightedPlayTime')->nullable();
            $table->integer('weightedPlays')->nullable();
            $table->integer('fastestPlayTime')->nullable();

            $table->double('rating')->nullable();
            $table->index('rating');
            $table->double('ratingWeek')->nullable();
            $table->index('ratingWeek');
            $table->double('ratingMonth')->nullable();
            $table->index('ratingMonth');
            $table->double('bayesianRating')->nullable();
            $table->foreign('bayesianRating')->references('rating')->on('missions')->onDelete('cascade');
            $table->double('bayesianRatingWeek')->nullable();
            $table->foreign('bayesianRatingWeek')->references('ratingWeek')->on('missions')->onDelete('cascade');
            $table->double('bayesianRatingMonth')->nullable();
            $table->foreign('bayesianRatingMonth')->references('ratingMonth')->on('missions')->onDelete('cascade');

            $table->integer('votes')->nullable();
            $table->integer('votesWeek')->nullable();
            $table->integer('votesMonth')->nullable();

            $table->integer( 'xpLevelTypeId');
            $table->integer('minXPLevel')->default(0);

            $table->string('panelStyle')->nullable();
            $table->date('reversalTime')->nullable();
            $table->date('timestamp')->nullable();
            $table->date('firstPlayed')->nullable();
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
        Schema::dropIfExists('missions');
    }
}

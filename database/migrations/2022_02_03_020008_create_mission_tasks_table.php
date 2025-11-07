<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_tasks', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(false);
            $table->string('title');
            $table->string('desc');
            $table->string('completedDesc');
            $table->string('script');
            $table->integer('timeLeft')->nullable(); // --> figure out how to use this
            $table->date('timestamp')->nullable();
            $table->bigInteger('missionId')->unsigned(); //mission_missionchain_id
            $table->foreign('missionId')->references('id')->on('missions');
            $table->integer('toTaskId')->nullable(); // When user add more than one task, update this value somehow...  (used in SMI)
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
        Schema::dropIfExists('mission_tasks');
    }
}

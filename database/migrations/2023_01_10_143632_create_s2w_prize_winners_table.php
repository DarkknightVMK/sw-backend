<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateS2wPrizeWinnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s2w_prize_winners', function (Blueprint $table) {
            $table->id();
            $table->string('avatarImage');
            // ref thumbUrl on avatars
            $table->string('avatarName');
            $table->string('avatarId');
            // ref avatarId on avatars
            $table->foreign('avatarId')->references('avatar_id')->on('avatars')->onDelete('cascade');
            $table->timestamp('prizeTime');
            // refernces id on s2w_prizes
            $table->unsignedBigInteger('prize');
            $table->foreign('prize')->references('id')->on('s2w_prizes');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            // add deluxeSpinsRemaining & cooldownTimeRemaining to users
            $table->double('deluxeSpinsRemaining')->nullable()->default(0.0);
            $table->timestamp('cooldownTimeRemaining')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('s2w_prize_winners');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deluxeSpinsRemaining');
            $table->dropColumn('cooldownTimeRemaining');
        });
    }
}

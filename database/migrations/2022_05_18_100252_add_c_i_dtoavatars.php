<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCIDtoavatars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
//     public function up()
//     {
//         Schema::table('avatars', function (Blueprint $table) 
//         {

//             $table->string('cid')->default('user.UserAvatar');
// //            $table->longText('stringId')->unique();
//         });

//         Schema::table('avatar_pets', function (Blueprint $table)
//         {
//             $table->string('cid')->default('ai.pet.PetAvatar');
//         });

//         Schema::create('ai', function (Blueprint $table) 
//         {
//             // avadroids are linked to npc item pads
//             $table->id();
//             $table->string('item_id')->unique(); // reference avatar_items.item_id
//             $table->foreign('item_id')->references('item_id')->on('avatar_items');
//             $table->string('cid')->default('ai.npc.NPCAvatar'); // ai.pet.PetAvatar for pets
//             $table->string('avatar_id')->length(32)->unique();
//             $table->string('firstName');
//             $table->string('lastName');
//             $table->string('gender');
//             $table->integer('nameInstance');
//             $table->string('fullName');
//             $table->string('motto')->nullable();
//             $table->longText('configString');
//             $table->longText('memoryString')->nullable();
//             // TODO
//             $table->longText('needs')->nullable();
//             $table->longText('needEffects')->nullable();
//             $table->longText('relationshipsWithAvatars')->nullable();
//             $table->longText('relationshipsWithItems')->nullable();
//             $table->longText('mood')->nullable();
//             // END TODO
//             $table->dateTime('dateCreated');
//             $table->boolean('active')->default(true);
//             $table->string('ownerId')->unique();
//             $table->foreign('ownerId')->references('avatar_id')->on('avatars')->onDelete('cascade');
//             $table->string('snapshotUrl');
//             $table->string('headUrl');
//             $table->string('thumbUrl');
//             $table->string('snapshotPostfix');
//             $table->string('headPostfix');
//             $table->string('thumbPostfix');
//             $table->timestamps();
//         });
//         Schema::table('avatar_items', function (Blueprint $table)
//         {
//             $table->string('npc_id')->limit('32')->unique()->nullable();
//             $table->foreign('npc_id')->references('avatar_id')->on('ai')->onDelete('cascade');
//         });

//         Schema::create('avatar_wearing', function (Blueprint $table) 
//         {
//             $table->string('avatar_id')->unique();
//             $table->foreign('avatar_id')->references('avatar_id')->on('avatars')->onDelete('cascade');
            
//             $table->string('item_id')->unique(); // reference avatar_items.item_id
//             $table->foreign('item_id')->references('item_id')->on('avatar_items');
//             $table->timestamps();
//         });

//         // Schema::table('avatar_spaces', function (Blueprint $table) 
//         // {
//         //     $table->integer('user_id')->unsigned();
//         //     $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
//         // });
//     }

//     /**
//      * Reverse the migrations.
//      *
//      * @return void
//      */
//     public function down()
//     {
//         Schema::table('avatars', function (Blueprint $table) {
//             $table->dropColumn('cid');
//         });

//         Schema::table('avatar_pets', function (Blueprint $table)
//         {
//             $table->dropColumn('cid');
//         });

//         Schema::dropIfExists('ai');

//         Schema::table('avatar_items', function (Blueprint $table)
//         {
//             $table->dropColumn('npc_id');
//         });

//     }
}

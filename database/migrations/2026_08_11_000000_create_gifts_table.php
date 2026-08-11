<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persistent log of item gifts — one row per delivered stack. This backs the
 * two-way gifting transaction view:
 *
 *   - `received` rows are pulled by the recipient and acked via `seen_at`, so a
 *     given gift surfaces in their log exactly once.
 *   - a like the recipient leaves (`liked_at`) is pulled back by the original
 *     sender and acked via `liked_seen_at`.
 *
 * This table is the durable source of truth that GET /api/gift/updates polls.
 * The Red5 socket push (giftReceived / giftLiked), when wired on the game
 * server, is just an instant mirror of the same data — the client dedupes by
 * gift id, so both paths can run at once.
 */
class CreateGiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gifts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sender_avatar_id');
            $table->string('recipient_avatar_id');
            $table->unsignedInteger('model_id');
            $table->unsignedInteger('count')->default(1);
            $table->string('giftMessage')->nullable();
            $table->timestamp('seen_at')->nullable();        // recipient's client fetched the "received" notice
            $table->timestamp('liked_at')->nullable();       // recipient liked the gift
            $table->timestamp('liked_seen_at')->nullable();  // sender's client fetched the "liked" notice
            $table->timestamps();

            $table->index(['recipient_avatar_id', 'seen_at']);
            $table->index(['sender_avatar_id', 'liked_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gifts');
    }
}

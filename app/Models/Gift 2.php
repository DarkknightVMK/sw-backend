<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

/**
 * One gifting transaction — a single stack of a model handed from the sender's
 * avatar to the recipient's. Written inside ItemsController::gift() alongside
 * the actual avatar_items transfer, then read back by the two-way gifting log
 * (see ItemsController::giftUpdates / giftLike).
 */
class Gift extends Model
{
    use HasFactory;

    protected $table = 'gifts';

    protected $fillable = [
        'sender_avatar_id',
        'recipient_avatar_id',
        'model_id',
        'count',
        'giftMessage',
        'seen_at',
        'liked_at',
        'liked_seen_at',
    ];

    protected $casts = [
        'seen_at'       => 'datetime',
        'liked_at'      => 'datetime',
        'liked_seen_at' => 'datetime',
    ];

    public function item() {
        return $this->belongsTo(items::class, 'model_id', 'model_id');
    }

    public function sender() {
        return $this->belongsTo(Avatars::class, 'sender_avatar_id', 'avatar_id');
    }

    public function recipient() {
        return $this->belongsTo(Avatars::class, 'recipient_avatar_id', 'avatar_id');
    }
}

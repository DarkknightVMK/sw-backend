<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class avatarItems extends Model
{
    use HasFactory;
    public $primaryKey = 'item_id';
    public $incrementing = false;
    
    protected $fillable = [
        'item_config',
        'item_count',
        'model_id',
        'item_id',
        'item_icon_postfix',
        'user_id',
        'avatar_id',
        'item_gifted_by_avatar',
        'giftOpenDate',
        'giftWrapModel_id',
        'giftMessage',
        'giftMessageUnwrap',
        'giftWrapModel_source'

        ];
        public $timestamps = false;

        public function user() {
            return $this->belongsTo(User::class, 'user_id');
        }

        public function items() {
            return $this->belongsTo(items::class, 'model_id');
        }

        public function space() {
            return $this->belongsTo(avatarSpaces::class, 'space_id');
        }

}

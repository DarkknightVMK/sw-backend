<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class items extends Model
{
    use HasFactory;
    public $primaryKey = 'model_id';
    public $incrementing = false;
    protected $fillable = [
        'model_details',
        'model_cid',
        'model_tags',
        'model_source',
        'model_desc', // title
        'model_id',
        // 'model_price',
        'model_icon',
        'model_min_xplevel',
        'model_price_gold',
        'model_price_tokens',
        'model_premium_only',
        'action_path',
        'hasAccessSecurity',
        'action_attributes',
        'action_attributes_secondary',
        'use_path',
        'baseItemId',
        ];
    public $timestamps = false;

    public function aitems() {
        return $this->hasMany(avatarItems::class);
    }

    public function scripts() {
        return $this->belongsTo(scripts::class, 'use_path');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Eloquent as Model;

class avatarSpaces extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'avatar_id',
        'modelId',
        'name',
        'config',
        'desc',
        'icon',
        'alias',
        'showInPlacePanel',
        'featuredIndex',
        'isShowroom',
        'maxInstances',
        'maxVisitors',
        'forSale',
        'salePriceTokens',
        'salePriceGold',
        'active',
        'indexed',
        'password',
        'instanceable',
        'type',
        'accessControl',
        'user_id',
        'lastPurchasedTokens',
        'lastPurchasedAmount',
        // Space-settings editor: visual theme + decor surfaces (short preset
        // keys, nullable → fall back to the space model's default look).
        'theme',
        'wallpaper',
        'flooring',


        ];
    protected $attributes = 
    [
        'windowWidgetConfig' => '<cycles><cycle scene_id="0" scene_time="1" scenery_id="0"/><cycle scene_id="0" scene_time="1" scenery_id="0"/><cycle scene_id="0" scene_time="1" scenery_id="0"/><cycle scene_id="0" scene_time="1" scenery_id="0"/><cycle scene_id="0" scene_time="1" scenery_id="0"/></cycles>'
    ];



//     public function user()
// {
//     return $this->belongsTo(User::class, 'avatar_id');
// }
public function avatar() {
    return $this->belongsTo(Avatars::class, 'avatar_id');
}

public function models() {
    return $this->belongsTo(spaceModels::class, 'model_id');
}

// Space-settings editor relationships. Members carry a role (space_roles);
// bans are the boot/block list. Both cascade-delete with the space at the
// DB level (see the space_members / space_bans migrations).
public function members() {
    return $this->hasMany(spaceMember::class, 'space_id', 'id');
}

public function bans() {
    return $this->hasMany(spaceBans::class, 'space_id', 'id');
}


}

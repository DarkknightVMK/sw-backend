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


}

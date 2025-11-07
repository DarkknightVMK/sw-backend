<?php
namespace App\result\space;

use App\Http\Services\functions;
use App\Models\onlineUsers;
use App\Models\spaceFavorites;
use App\result\space\Space;

#[\AllowDynamicProperties]
class SpacePanelItem extends Space
{
  public $_explicitType = 'com.smallworlds.entity.space.model.SpacePanelItem';
  public function __construct($space)
  {
    $this->lastPurchasedTokens = null; //todo
    $this->avatarDetails = null;
    $this->desc = $space->name;
    $this->avatarId = $space->avatar_id;
    $this->modelPriceTokens = null; // todo
    $this->spaceRoleAccess = '1'; // ? todo
    $this->votes = (float) 0; // todo
    $this->accessControl = $space->accessControl;
    $this->avatarFName = functions::getAvatarByID($space->avatar_id, 'firstName');
    $this->avatarLName = functions::getAvatarByID($space->avatar_id, 'lastName');
    $this->type = $space->type;
    $this->id = strval($space->id);
    $this->details = $space->desc;
    $this->lastPurchasedAmount = null; // todo
    $this->salePriceTokens = $space->salePriceTokens;
    $this->fav = spaceFavorites::where('avatar_id', session('avatar'))->where('space_id', $space->id)->exists();
    $this->currentVisitors = onlineUsers::where('space_id', $space->id)->where('online', true)->count();
    $this->thumbnailSource = $space->spaceThumbnailSource;
    $this->modelId = strval($space->modelId);
    $this->snapshotSource = $space->spaceSnapShotSource;
    $this->forSale = $space->forSale;
    $this->iconSource = $space->icon;
    $this->spaceOwner = strval($space->user_id);
    $this->modelPrice = $space->modelPrice;
    $this->salePrice = $space->salePriceGold; // gold
    $this->rating = (float) $space->rating;
    $this->spaceDetails = new Space($space);
    return $this;
  }

}
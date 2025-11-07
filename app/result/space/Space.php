<?php
namespace App\result\space;

#[\AllowDynamicProperties]
class Space
{
  public $_explicitType = 'com.smallworlds.entity.space.core.dao.data.Space';
  public function __construct($space)
  {
    $this->lastPurchasedTokens = null; //todo
    $this->isShowroom = $space->isShowroom;
    $this->desc = $space->name;
    $this->avatarId = $space->avatar_id;
    $this->indexed = $space->indexed;
    $this->ownerId = strval($space->user_id);
    $this->allowEmbedURLs = null;
    $this->joinChannelId = $space->spaceJoinChannel;
    $this->votes = (float) 0; // todo
    $this->accessControl = $space->accessControl;
    $this->type = $space->type;
    $this->showAds = $space->spaceShowAds;
    $this->instanceKey = (float) 0; // todo
    $this->id = strval($space->id);
    $this->lastPurchasedWithTokens = false;
    $this->lastPurchasedWithGold = false; // todo
    $this->details = $space->desc;
    $this->maxInstances = (float) 100;
    $this->accessPassword = null;
    $this->dirty = false;
    $this->bayesianRating = (float) 0;
    $this->config = $space->config;
    $this->worldId = '1';
    $this->joinChannel = false;
    $this->salePriceTokens = $space->salePriceTokens;
    $this->instanceable = $space->instanceable;
    $this->showInSearch = $space->spaceShowInSearch;
    $this->thumbnailSource = $space->spaceThumbnailSource;
    $this->salePriceGold = $space->salePriceGold; // gold
    $this->modelId = strval($space->modelId);
    $this->snapshotSource = $space->spaceSnapShotSource;
    $this->forSale = $space->forSale;
    $this->iconSource = $space->iconSource;
    $this->featuredIndex = $space->featuredIndex;
    $this->maxVisitors = $space->maxVisitors;
    $this->active = boolval($space->active);
    $this->rating = $space->rating;
    $this->showroom = $space->isShowroom; //bool
    return $this;
  }
}
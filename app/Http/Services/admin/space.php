<?php
include ('functions.php');
use App\Http\Services\functions;
use App\Models\avatarSpaces;
use App\Models\avatarItems;
use App\Models\spaceMember;
use App\Models\spaceRoles;
use App\result\ServiceResult;

  class space
  {
    function searchSpaces($timeconfig, $string, $spaceID, $spaceTitle, $OUID, $OAID, $alias, $featured, $fs, $active )
    {
      //  $OAID = $OUID;
      $amf = new stdClass();
      //var dump all the variables
      // var_dump($timeconfig);
      // var_dump($string);
      // var_dump($spaceID);
      // var_dump($spaceTitle);
      // var_dump($OUID);
      // var_dump($OAID);
      // var_dump($alias);
      // var_dump($featured);
      // var_dump($fs);
      // var_dump($active);
      $amf->recordSet = $this->search($spaceID, $spaceTitle, $OAID, $OUID);
      $amf->success=true;
      return $amf;
    }

    function saveSpaceDetails($timeconfig, $spaceID, $spaceTitle, $spaceDesc, $spaceType, $customIcon, $accessType, $accessPassword, $isInstanced, $maxVisitors, $maxInstances, $featuredIndex, $isShowroom, $isForSale, $fsGoldAmt, $fsTokensAmt, $indexed, $active, $showinPlaces, $config, $schStart, $schEnd)
    {
      $amf = new stdClass();
      avatarSpaces::where('id', intval($spaceID))->update(['name' => $spaceTitle, 'desc' => $spaceDesc, 'type' => $spaceType, 'icon' => $customIcon, 'accessControl' => $accessType, 'password' => ($accessPassword != null) ?bcrypt($accessPassword) : null, 'instanceable' => $isInstanced, 'maxVisitors' => $maxVisitors, 'maxInstances' => $maxInstances, 'featuredIndex' => $featuredIndex, 'isShowroom' => $isShowroom, 'forSale' => $isForSale, 'salePriceGold' => $fsGoldAmt, 'salePriceTokens' => $fsTokensAmt, 'indexed' => $indexed, 'active' => $active, 'showInPlacePanel' => $showinPlaces, 'config' => $config]);

      $amf->success=true;
      return $amf;
    }

    function saveSpaceAliasDetails($timeconfig, $spaceID, $alias)
    {
      $amf = new stdClass();
      $spaces = avatarSpaces::all();
      if ($alias != '')
      {
      // lower and no special characters
      $alias = strtolower(str_replace(' ', '', $alias));
      $alias = preg_replace('/[^a-z0-9_]/', '', $alias);
      // get rid of underscores 
      $alias = str_replace('_', '', $alias);
      $alias = preg_replace('/^[0-9]/', '', $alias);
      foreach ($spaces as $space) {

        if ($space->alias != $alias ) 
        {
          if (avatarSpaces::where('alias', $alias)->exists() && !avatarSpaces::where('id', $spaceID)->exists())
          {
            $amf->success=false;
            $amf->message='Alias already exists';
            return $amf;
          }
          avatarSpaces::where('id', $spaceID)->update(['alias' => $alias]);
          $amf->success=true;
        return $amf;
        }
        

      }
      // if ($alias != avatarSpaces::where('spaceID', $spaceID)->first()->alias) {      
      //   avatarSpaces::where('spaceID', $spaceID)->update(['alias' => $alias]);
        
      // }
    }
      $amf->success=true;
      return $amf;
    }

    
    function findSpace($timeconfig, $spaceID)
    {
      $spaces = avatarSpaces::where('id', $spaceID)->get();
      $amf = new stdClass();
      if ($spaces[0]['id'] != null)
      {
        
        $amf->id =$spaces[0]['id'];
        $amf->desc = $spaces[0]['name'] ;
        $amf->success=true;

      }
      else
        $amf->success=false;
      return $amf;
    }

    function getSpaceDetails($timeconfig, $spaceID)
    {
      $fnc = new functions();
      $spaces = avatarSpaces::where('id', $spaceID)->get();
      $amf = new stdClass();
      $homespace = false;
      if ($fnc->getAvatarByID($spaces[0]['avatar_id'], "homeSpaceId") == $spaceID)
        $homespace = true;
      $amf->id =$spaces[0]['id'];
      $amf->active = $spaces[0]['active'];
      $amf->indexed = $spaces[0]['indexed'];
      $amf->config = $spaces[0]['config'];
      $amf->forSale = $spaces[0]['forSale'];
      $amf->salePriceTokens = $spaces[0]['salePriceTokens'];
      $amf->salePriceGold = $spaces[0]['salePriceGold'];
      $amf->showInSearch = $spaces[0]['spaceShowInSearch'];
      $amf->featuredIndex = $spaces[0]['featuredIndex'];
      $amf->isShowroom = $spaces[0]['isShowroom'];
      $amf->maxInstances = $spaces[0]['maxInstances'];
      $amf->maxVisitors = $spaces[0]['maxVisitors'];
      $amf->isHomespace = $homespace;
      $amf->modelId = $spaces[0]['modelId'];
      $amf->modelDesc = $fnc->getModelByID($spaces[0]["modelId"],'model_desc');
      $amf->modelIcon = $fnc->getModelByID($spaces[0]["modelId"],'model_icon');
      $amf->modelPriceTokens = $fnc->getModelByID($spaces[0]["modelId"],'model_price_tokens');
      $amf->modelPriceGold = $fnc->getModelByID($spaces[0]["modelId"],'model_price');
      $amf->shopDisplayOrder = $spaces[0]['shopDisplayOrder'];
      $amf->accessControl = $spaces[0]['accessControl'];
      $amf->type= $spaces[0]['type'];
      $amf->ownerAvatarId = $spaces[0]['avatar_id'];
      $amf->ownerAvatarNameInstance = 1;
      $amf->ownerAvatarFName = "";
      $amf->ownerId = $spaces[0]['avatar_id'];
      $amf->details = $spaces[0]['desc'];
      $amf->desc = $spaces[0]['name'];
      $amf->alias = $spaces[0]['alias'];
      $amf->thumbnailFile = $spaces[0]['spaceThumbnailSource'];
      $amf->iconFile = $spaces[0]['icon'];
      $amf->success=true;
      return $amf;
    }

    function exportSpace($timeconfig, $spaceID)
    {
      $amf = new stdClass();
      $amf->data = "";
      $amf->success=true;
      return $amf;
    }
    function createSpace($timeconfig, $spaceModelID, $uid)
    {
      $amf = new stdClass();
      $amf->id = 1;
      $amf->config = "eNqzKS5ITE6141VQsMkvKMnMzysGsYG8ktSKktKiVBhXH5lvo4+k1qYkIzU3VSEzxVYpJTUtsTSnREnfjtdGH2owALUaHcw=";
      $amf->success=true;
      return $amf;
    }

    function search($spaceID, $spaceTitle,  $OAID, $OUID)
    {
        $avatar = new functions();
        // if ($spaceTitle != null){
        // $spaces = avatarSpaces::where('name','like', '%'. $spaceTitle .'%')->get();
        // }
        // var_dump($OAID);
        // if ($OUID != null && $OAID == null){
          // $spaces = avatarSpaces::where('avatar_id', )
        // }
        if ($OAID != null && $spaceID == null && $spaceTitle == null)
          $spaces = avatarSpaces::where('avatar_id', $OAID)->get();
        elseif ($OAID == null && $spaceID != null && $spaceTitle == null)
          $spaces = avatarSpaces::where('id', $spaceID)->get();
        elseif ($OAID == null && $spaceID == null && $spaceTitle != null)
          $spaces = avatarSpaces::where('name','like', '%'. $spaceTitle .'%')->get();
        elseif ($OAID != null && $spaceID != null && $spaceTitle == null)
          $spaces = avatarSpaces::where('avatar_id', $OAID)->where('id', $spaceID)->get();
        elseif ($OAID != null && $spaceID == null && $spaceTitle != null)
          $spaces = avatarSpaces::where('avatar_id', $OAID)->where('name','like', '%'. $spaceTitle .'%')->get();
        elseif ($OAID == null && $spaceID != null && $spaceTitle != null)
          $spaces = avatarSpaces::where('id', $spaceID)->where('name','like', '%'. $spaceTitle .'%')->get();
        elseif ($OAID != null && $spaceID != null && $spaceTitle != null)
          $spaces = avatarSpaces::where('avatar_id', $OAID)->where('id', $spaceID)->where('name','like', '%'. $spaceTitle .'%')->get();
        elseif ($OAID == null && $spaceID == null && $spaceTitle == null && $OUID != null)
          $spaces = avatarSpaces::where('user_id', $OUID)->get();
        else
          $spaces = avatarSpaces::where('id', $spaceID)->get();
        // $spaces = avatarSpaces::where('avatar_id', $OAID)->orWhere('id', $spaceID)->orWhere('name', 'like', '%' . $spaceTitle . '%')->get();
// var_dump($spaces);

        $ret = new stdClass();
        $count = count($spaces);
        //loop($count, $spaces);
        // var_dump($count);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $arr[] = array (

            'id' => $spaces[$i]["id"],

            'space' => array
            (
              "spaceOwner" => $spaces[$i]["avatar_id"],
              // 'avatarId' => $avatar->getAvatar('avatar_id'),
              // 'avatarFName' =>$avatar->getAvatar('firstName'),
              // 'avatarLName' =>$avatar->getAvatar('lastName'),
              
              "modelSale" => ($spaces[$i]["forSale"] == 'Y') ? true : false,
              "salePriceGold" => $spaces[$i]["salePriceGold"],
              "salePriceTokens" => $spaces[$i]["salePriceTokens"],
              "instanceSale" => ($spaces[$i]["forSale"] == 'I') ? true : false,
              'avatarDetails' => null,
              'desc' => $spaces[$i]["name"],
              'details' => $spaces[$i]["desc"],
              'type' => $spaces[$i]["type"],
              'iconUrl' => $spaces[$i]["icon"],
              'accessControl' => $spaces[$i]["accessControl"],
              'modelId' => strval($spaces[$i]["modelId"]),
              'spaceRoleAccess' => "0",
              'currentVisitors' => $spaces[$i]['currentVisitors'],
              "instanceId" => '',
              "thumbnailUrl" => $spaces[$i]["spaceThumbnailSource"],
              "snapshotUrl" => $spaces[$i]["spaceSnapshotSource"],
            ),

            'model' => array
            (
              'id' => strval($spaces[$i]["modelId"]),
              'isSpaceModel' => true,
              'isItemModel' => false,
              'icon' => $avatar->getModelByID($spaces[$i]["modelId"],'model_icon'),
              'desc' => $avatar->getModelByID($spaces[$i]["modelId"],'model_desc'),
              // 'desc' =>

            ),

            'avatar' => array
            (
              'id' => $spaces[$i]["avatar_id"],
              "nameInstance" => $avatar->getAvatarByID($spaces[$i]["avatar_id"],'nameInstance'),
              "firstName" => $avatar->getAvatarByID($spaces[$i]["avatar_id"],'firstName'),
              "lastName" => $avatar->getAvatarByID($spaces[$i]["avatar_id"],'lastName'),
              "isOnline" => false,
              "isDefault" => true,
              "hasPet" => false,
            ),

            'active' => $spaces[$i]["active"],
            'instanceable' => true,
            'featuredIndex' => $spaces[$i]["featuredIndex"],
        );

        }
        return $arr;
    }

    function cloneSpace($timeconfig, $spaceId)
    {
      // make sure the user has the permission to invoke this function! TODO
      // clone the space and all items within it
      $space = avatarSpaces::where('id', $spaceId)->first();
      // create new space
      $newSpace = new avatarSpaces;
      $newSpace->name = 'Copy of '. $space->name;
      $newSpace->desc = $space->desc;
      $newSpace->type = $space->type;
      $newSpace->icon = $space->icon;
      $newSpace->accessControl = $space->accessControl;
      $newSpace->modelId = $space->modelId;
      $newSpace->spaceThumbnailSource = $space->spaceThumbnailSource;
      $newSpace->spaceSnapshotSource = $space->spaceSnapshotSource;
      $newSpace->avatar_id = session('avatar');
      $newSpace->user_id = session('user');
      $newSpace->active = $space->active;
      $newSpace->featuredIndex = $space->featuredIndex;
      $newSpace->forSale = $space->forSale;
      $newSpace->salePriceGold = $space->salePriceGold;
      $newSpace->salePriceTokens = $space->salePriceTokens;
      $newSpace->windowWidgetConfig =  $space->windowWidgetConfig;
      $newSpace->showInPlacePanel = $space->showInPlacePanel;
      $newSpace->indexed = $space->indexed;
      $newSpace->maxVisitors = $space->maxVisitors;
      $newSpace->maxInstances = $space->maxInstances;
      $newSpace->password = $space->password;
      $newSpace->config = $space->config;
      $newSpace->save();
      // clone all items in the space
      $items = avatarItems::where('space_id', $spaceId)->get();
      foreach ($items as $item)
      {
        $newItem = new avatarItems;
        $newItem->space_id = $newSpace->id;
        $newItem->space_angle = $item->space_angle;
        $newItem->space_bounds = $item->space_bounds;
        $newItem->space_x = $item->space_x;
        $newItem->space_y = $item->space_y;
        $newItem->space_z = $item->space_z;
        $newItem->use = $item->use;
        $newItem->state = $item->state;
        $newItem->animation = $item->animation;
        $newItem->gridActive = $item->gridActive;
        $newItem->parentItemID = $item->parentItemID;
        $newItem->access = $item->access;
        $newItem->edit = $item->edit;
        $newItem->move = $item->move;
        $newItem->forSale = $item->forSale;
        $newItem->price = $item->price;
        $newItem->priceTokens = $item->priceTokens;
        $newItem->lastPurchasedTokens = $item->lastPurchasedTokens;
        $newItem->baseItemId = $item->baseItemId;
        $newItem->npc_id = null;
        $newItem->resetTime = $item->resetTime;
        $newItem->item_id = functions::generateRandomString(32);
        $newItem->user_id = session('user');
        $newItem->avatar_id = session('avatar');
        $newItem->model_id = $item->model_id;
        $newItem->item_count = $item->item_count;
        $newItem->item_config = $item->item_config;
        $newItem->save();
      }

      return new ServiceResult;
    }
  }
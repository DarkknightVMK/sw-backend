<?php

use App\Models\items;
use App\Models\avatarItems;
use App\Models\avatarSpaces;
use App\Models\Users;

class item
{
  function searchItems($timeconfig, $itemID, $uid, $modelId, $spaceID, $inventoryOnly, $unk2, $lastTranUID, $ShowGoldValue, $unk3, $unk4, $ShowTokenValue, $unk5, $unk6, $ShowNoValue, $showStacked, $showInactive, $showPlaceholder, $showNoSale, $showModelSale, $showInstanceSale, $showMarketSale, $giftedByUID, $limit, $searchNumber, $searchDateFrom, $searchDateTo)
  {
    // if limit if true show number 
        $amf = new stdClass();
        $amf->recordSet = $this->search($spaceID, $uid, $itemID, $searchNumber, $inventoryOnly, $modelId);
        $amf->success=true;

        return $amf;
  }

  function removeItems($itemID, $sendMail)
  {
    for ($i = 0; $i < count($itemID); $i++)
    {
      avatarItems::where('item_id', $itemID[$i])->delete();
    }

        $amf = new stdClass();
        // $amf->recordSet = $this->search($spaceID, $uid, $itemID, $searchNumber);
        $amf->success=true;
        return $amf;
  }
  function saveItemConfig($timeconfig, $itemId, $config)
  {
    $amf = new stdClass();
    avatarItems::where('item_id', $itemId)->update(['item_config' => $config]);
    $amf->success=true;
    return $amf;
  }

  function addItems($timeconfig, $modelID, $quantity, $user)
  {
    $amf = new stdClass();
    $uid = Users::where('id', intval($user))->get()->first();
    foreach ($modelID as $key => $model)
    {
      $model_id = intval($model);
      // $models = items::where('model_id', $model_id)->first();
      //add model to array 
      $models[] = $model_id;

      // var_dump(intval($value));


    // foreach($quantity as $key => $q)
    //   {

    //     // for ($i = 0; $i < $value; $i++)
    //     // {        

          
    //     // }
                  
    //   }        
    // var_dump(intval($quantity));

      // avatarItems::create(
      //     [
      //       'item_id' => $item_id,
      //       'model_id' => $model_id,
      //       'item_count' => intval($q),
      //       'avatar_id' => $uid->id,
      //       'item_config' => ""
      //     ]);
    }

    foreach ($quantity as $key => $q)
    {
      $q = intval($q);
      // add q to array
      $quantities[] = $q;


    }
          // var_dump($quantities);
          foreach ($models as $key => $model)
          {
            $item_id = $this->create_guid();
            $model_id = intval($model);
            $q = $quantities[$key];
            avatarItems::create(
            [
              'item_id' => $item_id,
              'model_id' => $model_id,
              'item_count' => $q,
              // 'avatar_id' => $uid->choosen,
              'item_config' => "",
              '',
              'user_id' => $uid->id
            ]);


            // var_dump($model_id);
          }

    $amf->success=true;
    return $amf;
  }

  function create_guid()
        {
            $charid = md5(uniqid(mt_rand(), true));
            $hyphen = chr(45);// "-"
            $uuid =
                substr($charid, 0, 8)
                . substr($charid, 8, 4)
                . substr($charid, 12, 4)
                . substr($charid, 16, 4);
            return $uuid;
        }

  function removeItemsFromSpace($itemID)
  {
    $client = new SabreAMF_Client("https://".SITE_DOMAIN."/java/swds/gateway"); // Set up the client object
    for ($i = 0; $i < count($itemID); $i++)
    {
      avatarItems::where('item_id', $itemID[$i])->update([
      'space_id' => null,
      'space_x' => null,
      'space_y' => null,
      'space_z' => null,
      'space_angle' => null,
      'space_bounds' => null,
  ]);
}
      $client->sendRequest('ds.removeItemFromSpace', array(session('avatar'), $itemID)); 

      $amf = new stdClass();
      // $amf->recordSet = $this->search($spaceID, $uid, $itemID, $searchNumber);
      $amf->success=true;
      return $amf;
  }


  function search($spaceID, $uid, $itemID, $searchNumber, $inventoryOnly, $modelId)
    {
      $onlySpaces = false;
      $both = false;
        if ($uid != null && $spaceID == null && $itemID == null && $inventoryOnly == false)
        {
          $both = true;

          if ($modelId)
            $items = avatarItems::where('user_id', $uid)->where('model_id', $modelId)->get();
          else
            $items = avatarItems::where('user_id', $uid)->get();

    
        }
        elseif ($uid != null && $spaceID == null && $itemID == null && $inventoryOnly == true)
        {
          $items = avatarItems::where('avatar_id', $uid)->where('space_id', '=', null)->get();
        }
        elseif ($uid != null && $spaceID != null && $itemID == null && $inventoryOnly == false)
        {
          $onlySpaces = true;
        $items = avatarItems::where('avatar_id', $uid)->where('space_id', '=', $spaceID)->get();
        }
        elseif ($uid == null && $spaceID != null && $itemID == null && $inventoryOnly == false)
        {
          $onlySpaces = true;
        $items = avatarItems::where('space_id', '=', $spaceID)->get();
        }
        //  var_dump($items);
        // $ret = new stdClass();
        $count = count($items);
        //loop($count, $spaces);
        // var_dump($count);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
          $models = items::where('model_id', $items[$i]["model_id"])->get();
          // ITEM IN SPACE
          if ($items[$i]['space_id'] != null)
            {
              $spaces =  avatarSpaces::where('id', $items[$i]['space_id'])->get();
              
              $arr[] = array 
              (
                'id' => $items[$i]["item_id"],
                'modelId' => $items[$i]["model_id"],
                'modelTags' => $models[0]["model_tags"],
                'modelDesc' => $models[0]["model_desc"],
                'modelIcon' => $models[0]["model_icon"],
                'count' => $items[$i]["item_count"],
                'stackCount' => 0,
                'inMarketplace' => false,
                'config' => $items[$i]['item_config'],

    
                  'itemInfo' => array
                  (
                    'id' => $items[$i]["item_id"],
                    'modelId' => $items[$i]["model_id"],
                    'modelTags' => $models[0]["model_tags"],
                    'modelDesc' => $models[0]["model_desc"],
                    'modelIcon' => $models[0]["model_icon"],
                    'instanceSale' => false,
                    'active' => true,
                  ),
    
                  'owner' => array
                  (
                    'id' => strval($items[$i]["avatar_id"]),
                  ),
    
                'active' => true,
                'instanceable' => false,

              'space' => array
              (
                'spaceOwner' => $spaces[0]["avatar_id"],
                // 'avatarId' => $spaces,
                // 'avatarFName' =>$avatar->getAvatar('firstName'),
                // 'avatarLName' =>$avatar->getAvatar('lastName'),
                'avatarDetails' => null,
                'desc' => $spaces[0]["name"],
                'details' => $spaces[0]["desc"],
                'type' => "A",
                'forSale' => "N",
                'iconSource' => $spaces[0]["icon"],
                'accessControl' => $spaces[0]["accessControl"],
                'modelId' => strval($spaces[0]["modelId"]),
                'spaceRoleAccess' => "0",
                'currentVisitors' => $spaces[0]['currentVisitors'],
                "instanceId" => 0,
              ),

              );
            }

          // INVENTORY
          if ($onlySpaces == false  && $items[$i]['space_id'] == null)
          {
          $arr[] = array 
          (

            'id' => $items[$i]["item_id"],
            'modelId' => $items[$i]["model_id"],
            'modelTags' => $models[0]["model_tags"],
            'modelDesc' => $models[0]["model_desc"],
            'modelIcon' => $models[0]["model_icon"],
            'count' => $items[$i]["item_count"],
            'stackCount' => 0,
            'inMarketplace' => false,
            'config' => $items[$i]['item_config'],

              'itemInfo' => array
              (
                'id' => $items[$i]["item_id"],
                'modelId' => $items[$i]["model_id"],
                'modelTags' => $models[0]["model_tags"],
                'modelDesc' => $models[0]["model_desc"],
                'modelIcon' => $models[0]["model_icon"],
                'instanceSale' => false,
                'active' => true,
              ),

              'owner' => array
              (
                'id' => strval($items[$i]["avatar_id"]),
              ),

            'active' => true,
            'instanceable' => false,
          );
        }
        }
        return $arr;
    }
}
<?php

use App\Models\items;
use App\Models\avatarItems;
use App\Models\Users;
use App\Models\catalogCategories;
use App\Models\catalogItemEntry;
use SabreAMF\Client;
use SabreAMF\CallbackServer;
use App\Http\Services\user\balance;
use RTMPClient as RtmpClient;

class entry
{

  function getColorOptionForEntry($timeconfig, $currentEntry, $modelId)
  {
    //find entry for modelId and return it
    $entry = catalogItemEntry::where('modelId', $modelId)->get();
    $amf = new stdClass();
    foreach ($entry as $entry)
    {
      $items = items::where('model_id', $entry->modelId)->first();
      // $entry->type = '';
      $amf->priceGold =  ($entry->salePriceGold == 0) ? 10 : $entry->salePriceGold;
      $amf->vipOnly = $items->model_premium_only; 
      $amf->startTime = null;
      $amf->stopTime = null;
      $amf->timeLeft = 0;    
      $amf->icon = $items->model_icon;
      $amf->priceTokens = $items->model_price_tokens; 
      $amf->name = $items->model_desc;
      $amf->active = boolval($entry->active);
      $amf->modelClassId = $items->model_cid;
      $amf->modelSource = $items->model_source;
      $amf->icon = $items->model_icon;
      // $entry->modelIcon = $items->model_icon; <-- use for older mains!
      $amf->modelIconUrl = $items->model_icon;
      $amf->modelDesc = $items->model_desc;
      $amf->desc = $items->model_details;
      $amf->modelDetails = $items->model_details;
      $amf->modelTags = $items->model_tags;
      $amf->modelPriceGold = $items->model_price_gold;
      $amf->modelPriceTokens = $items->model_price_tokens;
      $amf->modelAllowedCount = 0;
      $amf->modelMinXPLevel = $items->model_min_xplevel;
      $amf->isBundleEntry = false;
      $amf->isSingleItemEntry = true;
      $amf->isSingleCPEntry = false;
      $amf->discounted= $entry->discounted;
      $amf->id = $entry->id;
      $amf->shareDiscount = $entry->shareDiscount;
      $amf->quantity = $entry->quantity;
      $amf->modelId = $entry->modelId;
      // $amf = $entry->toArray();
      // array values


      // $entry->isSingleEntry = true; <-- use for older mains!
      }
      // $amf->array_keys($entry->toArray()) =  $entry;

       // amf points to all entry keys and values
            // $amf->{array_keys($entry->toArray())} = array_values($entry->toArray());

      // $amf->list[] = array ('childEntries' => $entry->toArray());
      //print entry for each amf
      $amf->success = true;
      return $amf;



  }
  function getActiveEntriesForCategory($timeconfig, $catID)
  {
    $amf = new stdClass();
    //ignore active for now
    $category = catalogCategories::where('id', $catID)->first();
    $itemEntry = catalogItemEntry::where('categoryId', $catID)->get();
    // $items = items::where('model_id', $itemEntry->modelId)->get();
    // 'name' => $items[$i]['model_desc'],
    // 'vipOnly' => $items[$i]['model_premium_only'],
    // 'priceTokens' => $items[$i]['model_price_tokens'],
    // 'priceGold' => ($items[$i]['model_price_gold'] == null) ? 10 : $items[$i]['model_price_gold'],
    // 'orderIndex' => 1,
    // 'icon' => $items[$i]['model_icon'],
    // 'startTime' => null,
    // 'stopTime' => null,
    // 'timeLeft' => 0,
    // 'type' => 'item',
    // 'id' => $items[$i]['model_id'],
    // 'shareDiscount' => 0,
    // 'active' => true,
    // 'modelId' => $items[$i]['model_id'],
    // 'modelClassId' => $items[$i]['model_cid'],
    // 'modelSource' => $items[$i]['model_source'],
    // 'modelIcon' => $items[$i]['model_icon'],
    // 'modelIconUrl' => $items[$i]['model_icon'],
    // 'modelDesc' => $items[$i]['model_desc'],
    // 'modelDetails' => $items[$i]['model_details'],
    // 'modelTags' => $items[$i]['model_tags'],
    // 'modelPriceGold' => $items[$i]['model_price_gold'],
    // 'modelPriceTokens'=>  $items[$i]['model_price_tokens'],
    // 'modelAllowedCount' => 0,
    // 'modelMinXPLevel' => $items[$i]['model_min_xplevel'],
    // 'isBundleEntry' => false,
    // 'isSingleEntry' => true,
    // 'isSIngleCPEntry' => false
    foreach ($itemEntry as $entry)
    {
      if ($entry->type != 'C')   
      {
      $items = items::where('model_id', $entry->modelId)->first();
      // $entry->type = '';
      $entry->priceGold =  ($entry->salePriceGold == 0) ? (float) 10 : (float) $entry->salePriceGold;
      $entry->vipOnly = boolval($items->model_premium_only); 
      $entry->startTime = null;
      $entry->stopTime = null;
      $entry->timeLeft = 0;    
      $entry->icon = $items->model_icon;
      $entry->priceTokens = (float) $items->model_price_tokens; 
      $entry->name = $items->model_desc;
      $entry->active = boolval($entry->active);
      $entry->shareDiscount = (float)$entry->shareDiscount;
      $entry->discounted = boolval($entry->discounted);
      $entry->quantity = (float)$entry->quantity;
      $entry->modelClassId = $items->model_cid;
      $entry->modelSource = $items->model_source;
      $entry->icon = $items->model_icon;
      // $entry->modelIcon = $items->model_icon; <-- use for older mains!
      $entry->modelIconUrl = $items->model_icon;
      $entry->modelDesc = $items->model_desc;
      $entry->desc = $items->model_details;
      $entry->modelDetails = $items->model_details;
      $entry->allowedCount = (float) $entry->modelAllowedCount;
      $entry->childEntries = array();
      $entry->modelTags = $items->model_tags;
      // $entry->modelPriceGold = (float) $items->model_price_gold; // <-- use for older mains!
      $entry->originalPriceGold = (float) $items->model_price_gold;
      $entry->originalPriceTokens = (float) $items->model_price_tokens;
      // $entry->modelPriceTokens = (float) $items->model_price_tokens; // <-- use for older mains!
      $entry->modelAllowedCount = 0;
      $entry->modelMinXPLevel = (float) $items->model_min_xplevel;
      $entry->isBundleEntry = false;
      $entry->isSingleItemEntry = true;
      // $entry->isSingleEntry = true; <-- use for older mains!
      }
      else
      {
        // $entry->id = $entry->cp;
        $entry->active = boolval($entry->active);
        $entry->priceGold =  ($entry->salePriceGold == 0) ? (float) 10 : (float) $entry->salePriceGold;
        $entry->priceTokens = 0;
        $entry->name = $entry->cp/20 . ' ' . $entry->categoryName . ' ('.$entry->cp .' Citizen Points)';
        $entry->modelId = (float) $entry->cp;
        $entry->cp = (float) $entry->cp;
        $entry->startTime = null;
        $entry->quantity = (float) $entry->quantity;
      $entry->stopTime = null;
      $entry->timeLeft = 0;    
      }
      ($entry->type == 'C') ? $entry->isSingleCPEntry = true : $entry->isSingleCPEntry = false;
      ($entry->type == 'standard') ? $entry->isSingleItemEntry = true : $entry->isSingleItemEntry = false;
      ($entry->type == 'B') ? $entry->isBundleEntry = true : $entry->isBundleEntry = false;

      $amf->list[] = $entry->toArray();
    }
    // 1 = new in, 2 = consumables 3 = Everything else
    // if ($catID == 1)
    // {
    // }
    // if ($catID == 2)
    // {
    //   $amf->list = $this->getItems('wear');
    // }
    if ($catID == 999)
    {
      $amf->list = $this->getItems('everything');
    }
    // if ($catID == 4)
    //   $amf->list = $this->getItems('consumable');
    // if ($catID == 5)
    // {
    //   $amf->list = $this->getItems('clothing');

    // }
    // if ($catID == 6)
    //   $amf->list = $this->getItems('accessory');
    // if ($catID == 7)
    //   $amf->list = $this->getItems('custom');
    //   if ($catID == 8)
    //   $amf->list = $this->getItems('admin');
    $amf->success = true;
    return $amf;
  }
  public function getUser($json = null)
  {
      $user_json = Users::where('id', session('user'))->get();
      $user_json = $user_json->toJson();
      preg_match_all('/\{(?:[^{}]|(?R))*\}/', $user_json, $jsondecode);
      $user = json_decode($jsondecode[0][0], true);
      if ($json == null)
          return $user;
      return $user[$json];
  }
  function purchaseEntry($timeconfig, $entryId, $quantity, $currency, $param1, $config,$config2,$modelId)
  {
    $user = Users::find(session('user'));
    // if ($user->serverIP != '127.0.0.1')
    //   $client = new SabreAMF_Client("https://SITE_DOMAIN/java/swds/gateway;jsessionid=".session('id')); // Set up the client object
    // else
    //   $client = new SabreAMF_Client("https://SITE_DOMAIN/localhost/swds/gateway;jsessionid=".session('id')); // Set up the client object
    // var_dump($client);
    $entry = catalogItemEntry::find($entryId);
	
	// --- Handle purchasing of CP in the catalog.
    if ($entry->type == 'C')
    { // figure out cp
	  
	  // CP should only be listed with a price in GOLD. Token CP purchases are unsupported.
      if ($currency == 'gold')
       {
         if ($entry == null)
         {
           $price = 10;
           Users::where('id', session('user'))->decrement('goldBalance', $price);
         }
         else{
         $entry->salePriceGold = $entry->salePriceGold * $quantity;
          Users::where('id', session('user'))->decrement('goldBalance', $entry->salePriceGold);
         }
       }
       if ($quantity > 1)
       {
        $entry->cp = $entry->cp * $quantity;
        $level = $entry->cp / 20;
        Users::where('id', session('user'))->increment('citizenPoints', $entry->cp);
        Users::where('id', session('user'))->increment('citizenLevel', $level);
       }
       else
       {
        $level = $entry->cp / 20;
        Users::where('id', session('user'))->increment('citizenPoints', $entry->cp);
        Users::where('id', session('user'))->increment('citizenLevel', $level);
       }
      $amf = new stdClass();
    $amf->purchasedQuantity = (float) $quantity;
    $amf->baseItemId = $entryId;
    $amf->newCp = (float) $entry->cp;
    $amf->success = true;
    // $client->sendRequest('ds.updateBalance', array(session('user'), true, session('id'), session('avatar'))); 
    // $client->sendRequest('ds.updateBalance', array(session('user'), false, session('id'), session('avatar'))); 

    return $amf;
    }

    $baseItemId = items::where('model_id', $entry->modelId)->first()->baseItemId;
    
      $modelID = $entry->modelId;
    
    function createItem($item_id, $ic, $model_id, $aid, $config, $config2)
       {
        //  if ($config2 != null)
          
        avatarItems::create(
            [
                'item_id' => $item_id,
                'model_id' => $model_id,
                'item_count' => $ic,
                'user_id' => session('user'),
                'avatar_id' => session('avatar'),
                'item_config' => ($config == "" || $config == null) ? "" : $config,
                'item_icon_postfix' => ($config2 == null) ? null : uploadIcon($config2,$item_id, generateRandomString(10)),
            ]
        );
  
       } 
       if ($currency == 'gold')
       {
         $entry = catalogItemEntry::where('modelId', $modelID)->first();
         if ($entry == null)
         {
           $price = 10;
           Users::where('id', session('user'))->decrement('goldBalance', $price);
         }
         else{
         $entry->salePriceGold = $entry->salePriceGold * $quantity;
          Users::where('id', session('user'))->decrement('goldBalance', $entry->salePriceGold);
         }
       }
       else if ($currency == 'tokens')
       {
        $entry = catalogItemEntry::where('modelId', $modelID)->first();
        $item = items::where('model_id', $modelID)->first();
        if ($entry == null)
        {
          $price = 10;
          Users::where('id', session('user'))->decrement('tokenBalance', $price);
        }
        else{
        $item->model_price_tokens = $item->model_price_tokens * $quantity;
         Users::where('id', session('user'))->decrement('tokenBalance', $item->model_price_tokens);
        }
       }
	  function uploadIcon($image, $item_id, $postfix)
	  {

		// upload icon
		$image = str_replace('data:image/png;base64,', '', $image);
		$image = str_replace(' ', '+', $image);


		$filename = 'iconbmp_'.$item_id.'_'. $postfix .'.png';
		// var_dump($filename);
		
		Storage::disk('widgets')->put( $filename,  base64_decode($image));
		return $postfix;

	  }
	  function generateRandomString($length = 10) 
	  {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
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

        $id = session('avatar');
        $item_id = create_guid();
        if ($quantity > 1)
        {
          for ($i = 0; $i < $quantity; $i++)
          {
            createItem(create_guid() , 1, intval($modelID), $id, $config, $config2);
            // if($config2 != null)
            // {
            //   // $config2 = hexdec($config2);
            //   $this->uploadIcon($config2, $modelID, create_guid());
            // }
          }
        }
        else
        {
          createItem($item_id , 1, intval($modelID), $id, $config, $config2);
        }
      //  getMyAccountBalances();
    $amf = new stdClass();
    $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
    $amf->$explicitTypeField = "com.smallworlds.catalog.entry.external.amf.result.EntryPurchaseResult";
    $amf->purchasedQuantity = $quantity;
    $amf->baseItemId = $item_id;
    // $amf->newCp = (float)null;

    $amf->success = true;
    // $client->sendRequest('ds.updateBalance', array(session('user'), false, session('id'), session('avatar'))); 

        // call  https://SITE_DOMAIN/java/swds/gateway via curl
  
        // $client->sendRequest('user.balance.getMyAccountBalances', array(session('user'), true, session('id'))); 
        // $client->call("ds.updateBalance", array($timeconfig, $userId));
        // var_dump($client);

        
        
        // $server = new SabreAMF_CallbackServer();
        // $server->setBaseClassPath( "../Services/" );
        //     $server->onInvokeService = 'myCallBack';

        //   $server->exec();
        // var_dump($client); //Dump the results
        // $bal = new balance();
        // balance::getMyAccountBalances();
        // $bal->getMyAccountBalances();
        // $this->invokeService('user.balance', 'getMyAccountBalances', 1);
        // $function = new ReflectionFunction();
        // $function->invoke();

    return $amf;


   


      


  }
//   function invokeService($service,$method,$arguments) {

//     // first check if the current user has the correct permissions to 
// // invoke the method
//     // if not, throw an exception

//     // Most likely you want to load a class after that, and call its 
// // method with
//     return call_user_func_array(array($object,$method),$arguments);

// }
  

    function getItems($sort)
    {
      if ($sort == 'wear')
      {
        $items = items::where('model_tags', 'LIKE', "%$sort%")->where('model_tags', 'NOT LIKE', '%clothing%')->where('model_source', 'NOT LIKE', '%accessory%')->get();
      }
      elseif ($sort == 'consumable')
      {
        $items = items::where('model_tags', 'LIKE', "%$sort%")->where('model_tags', 'NOT LIKE', '%wear%')->get();
      }
      elseif($sort == 'clothing')
      {
        $items = items::where('model_tags', 'LIKE', "%$sort%")->get();
      }
      elseif ($sort == 'accessory')
      {
        $items = items::where('model_source', 'LIKE', '%accessory%')->get();
      }
      elseif ($sort == 'custom')
      {
        $items = items::where('model_tags', 'LIKE', '%custom%')->get();
      }
      elseif ($sort == 'admin')
      {
        $items = items::where('model_tags', 'LIKE', '%admin%')->get();
      }
      else
        $items = items::all();
      $count = count($items);

      $amf = new stdClass();
      $arr = array();
      
      for ($i = 0; $i < $count; $i++)
      {
        $arr[] = array (
          'name' => $items[$i]['model_desc'],
          'vipOnly' => $items[$i]['model_premium_only'],
          'priceTokens' => $items[$i]['model_price_tokens'],
          'priceGold' => ($items[$i]['model_price_gold'] == null) ? 10 : $items[$i]['model_price_gold'],
          'orderIndex' => 1,
          'icon' => $items[$i]['model_icon'],
          'startTime' => null,
          'stopTime' => null,
          'timeLeft' => 0,
          'type' => 'item',
          'id' => $items[$i]['model_id'],
          'shareDiscount' => 0,
          'active' => true,
          'modelId' => $items[$i]['model_id'],
          'modelClassId' => $items[$i]['model_cid'],
          'modelSource' => $items[$i]['model_source'],
          'icon' => $items[$i]['model_icon'],
          'modelIconUrl' => $items[$i]['model_icon'],
          'modelDesc' => $items[$i]['model_desc'],
          'modelDetails' => $items[$i]['model_details'],
          'modelTags' => $items[$i]['model_tags'],
          'modelPriceGold' => $items[$i]['model_price_gold'],
          'modelPriceTokens'=>  $items[$i]['model_price_tokens'],
          'modelAllowedCount' => 0,
          'modelMinXPLevel' => $items[$i]['model_min_xplevel'],
          'isBundleEntry' => false,
          'isSingleItemEntry' => true,
          'isSIngleCPEntry' => false



        );

      
      }
      return $arr;

    }



}
<?php
// include_once ('../functions.php');
use App\Models\items;
use App\Models\avatarItems;
use App\Models\Avatars;
use App\Models\Users;
use App\result\ServiceResult;
use SabreAMF\Client;
use SabreAMF\CallbackServer;
use App\result\BooleanResult;
use App\result\StringResult;
use App\Http\Services\functions;

class item 
{
  function viewMyInventory()
  {


        $items = avatarItems::where('user_id',  session('user'))->join('items', 'items.model_id', '=', 'avatar_items.model_id')->where('space_id','=', null)->get();



        $count = count($items);
        $ret = new stdClass();
// $models = items::where('model_id', $items[0]["model_id"])->get();
// echo $models[0]["model_id"];
        $arr = array();
        $arr[] = array (
          'model_cid',
          'item_config',
          'model_tags',
          'model_details',
          'avatar_fname',
          'model_source',
          'avatar_thumb_postfix',
          'item_icon_postfix',
          'model_desc',
          'gift_wrap_model_source',
          'item_gifted_by_avatar',
          'item_last_purchased_with_model_id',
          'item_id',
          'model_id',
          'avatar_lname',
          'item_last_purchased_amount',
          'model_premium_only',
          'model_price_tokens',
          'item_purchased_with_tokens',
          'item_count',
          'item_last_purchased_tokens',
          'model_price',
      );
        foreach($items as $item)
{
    // filter out if the item has same model_id
    // $arr[] = $item;
    // if (count($arr) > 1)
    // {
      
    // }
    // $models = items::where('model_id', $items[$i]["model_id"])->get();
    // if items.model_id is the 

$arr[] = array (
    //model_cid
    $item->model_cid,
    //item_config
    $item->item_config,
    //model_tags
    $item->model_tags,
    //model_details
    $item->model_details,
    //avatar_fname
    null,
    //model_source
    $item->model_source,
    //avatar_thumb_postfix
    null,
    //item_icon_postfix
    $item->item_icon_postfix,
    //model_desc
    $item->model_desc,
    //gift_wrap_model_source
   null,
    //item_gifted_by_avatar
    $item->item_gifted_by_avatar,
    //item_last_purchased_with_model_id
    null,
    //item_id
    $item->item_id,
    //model_id
   strval($item->model_id),
    //avatar_lname
    null,
    null, //item_last_purchased_amount
    boolval($item->model_premium_only), //model_premium_only
    // model_price_tokens
    (float)$item->model_price_tokens,
    //item_purchased_with_tokens
    null,
    //item_count
   (float) $item->item_count,
    //item_last_purchased_tokens
    null,
    //model_price
    (float)$item->model_price,
);
    
}

    $ret = new stdClass();
    $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
    $ret->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";
    $ret->startIndex = null;
    $ret->maxLength = null;
    $ret->totalCount = 0;

    $ret->recordSet = $arr;
    $ret->success= true;
       return $ret;
    
  }

  function getMyEmotes()
  {
    return new ServiceResult();
  }

  function giftWrapItem($timeconfig, $itemId, $q, $receiptAid, $msg, $wrapModelId, $msgUnwrap, $date)
  {
    $item = avatarItems::where('item_id', $itemId)->first();
    if ($item == null)
    {
      return new ServiceResult(false);
    }
    $item->giftOpenDate = $date;
    $item->giftWrapModel_id = $wrapModelId;
    $item->giftMessage = $msg;
    $item->giftMessageUnwrap = $msgUnwrap;
    $item->item_gifted_by_avatar = session('avatar');
    $item->avatar_id = $receiptAid;
    $item->user_id = Avatars::where('avatar_id', $receiptAid)->first()->owner_id;
    $item->giftWrapModel_source = items::where('model_id', $wrapModelId)->first()->model_source;
    $item->save();

    return new ServiceResult;
  }

  function getModelSource($timeconfig, $modelId)
  {
    // get model source from modelId
    $modelSource = items::where('model_id', $modelId)->first()->model_source;
    return new StringResult($modelSource);
  }

  function giftItem($timeconfig, $itemId, $q, $receiptAid, $msg)
  {
    $item = avatarItems::where('item_id', $itemId)->first();
    if ($item == null)
    {
      return new ServiceResult(false);
    }
    $item->item_gifted_by_avatar = session('avatar');
    $item->avatar_id = $receiptAid;
    $item->user_id = Avatars::where('avatar_id', $receiptAid)->first()->owner_id;
    $item->giftMessage = $msg;
    $item->save();
    return new ServiceResult;
  }

  function updateItemLinks($timeconfig, $itemID, $spaceID)
  {
      $amf = new stdClass();
        $amf->success = true;
        return $amf;
  }
  function refundItem($timeconfig, $itemID, $quantity)
  {
    // $client = new SabreAMF_Client("https://SITE_DOMAIN/java/swds/gateway;jsessionid=".session('id')); // Set up the client object
      $amf = new stdClass();
      $item = avatarItems::where('item_id', $itemID)->get()->first();
      $model = items::where('model_id', $item->model_id)->get()->first();
      if ($model->model_price_gold > $model->model_price_tokens)
      {
        //refund gold
        $gold = $model->model_price_gold /2;
        Users::where('id', session('user'))->increment('goldBalance', $gold);

      }
      else
      {
        //refund tokens
        $tokens = $model->model_price_tokens /2;
        Users::where('id', session('user'))->increment('tokenBalance', $tokens);
      }
      $item->delete();
        $amf->success = true;

        $client->sendRequest('ds.updateBalance', array(session('user'), false, session('id'), session('avatar'))); 
        return $amf;

        

  }
  function destroyItem($timeconfig, $itemID, $quantity)
  {

      $amf = new stdClass();
      $item = avatarItems::where('item_id', $itemID)->get()->first();
      
      $item->delete();
        $amf->success = true;
        return $amf;

  }

  function incrementWidgetUses($timeconfig, $itemID, $time)
  {
      $amf = new stdClass();
      $amf->success = true;
      return $amf;
  }

  function getMyItems($timeconfig, $tag, $bool)
  {
    $amf = new stdClass();
    $amf->success = true;
    $amf->items =$this->getItemDetails($tag);
    return $amf;
  }
  function getModels($timeconfig, $tag)
  {
    $amf = new stdClass();
    $amf->success = true;
    $amf->models =$this->getModelDetails($tag);
    return $amf;
  }

  function getModelDetails($tag)
  {
    $model = items::where('model_tags', 'like','%'.$tag.'%')->get();
    $count = count($model);
    $ret = new stdClass();
    $arr = array();
    $k = 1;
    for ($i = 0; $i < $count; $i++)
        {
            $arr[] = array (
                'id' => $model[$i]["model_id"],
                'icon' => $model[$i]["model_icon"],
                'source' => $model[$i]["model_source"],
                'type' => $model[$i]["model_type"],
                'tags' => $model[$i]["model_tags"],
                'desc' => $model[$i]["model_desc"],
                'accessControl' => $model[$i]["access"],
                'moveControl' => $model[$i]["move"],
                'editControl' => $model[$i]["edit"],
                'priceGold' => $model[$i]["model_price_gold"],
                'priceTokens' => $model[$i]["model_price_tokens"],
                'success' => true
            );
        }
    return $arr;

  }

  function getItemDetails($tag)
  {
    $user = Users::where('id', session('user'))->get();
    $aitems = avatarItems::where('user_id', session('user'));
    $items = avatarItems::where('user_id',  session('user'))->join('items', 'items.model_id', '=', 'avatar_items.model_id')->where('items.model_tags', 'like', '%'.$tag.'%')->get();
    $count = count($items);
    $ret = new stdClass();
    $arr = array();
    $k = 1;
 
    $model = items::where('model_tags', 'like','%emote%')->get();

    for ($i = 0; $i < $count; $i++)
        {

                $arr[] = array ("item" => array (
                    'count' => $items[$i]["item_count"],
                    'modelPriceTokens' => $items[$i]["model_price_tokens"],
                    'modelClassId' => $items[$i]["model_cid"],
                    'modelTags' =>$items[$i]["model_tags"],
                    'giftedByAvatarLastName' => null,
                    'modelAllowedCount' => 0,
                    'iconPostfix' => $items[$i]["item_icon_postfix"],
                    'lastPurchasedWithTokens' => $items[$i]["item_last_purchased_tokens"],
                    'modelSource' => $items[$i]["model_source"],
                    'modelDetails' => $items[$i]["model_details"],
                    'modelDesc' => $items[$i]["model_desc"],
                    'config' => $items[$i]["item_config"],
                    'giftedByAvatarId' => null,
                    'hasPersistedConfig' => false,
                    'giftWrapModelSource' => $items[$i]["giftWrapModel_source"],
                    'modelMinXPLevel' => $items[$i]["model_min_xplevel"],
                    'modelPriceGold' => $items[$i]["model_price_gold"],
                    'id' => $items[$i]["item_id"],
                    'modelId' => $items[$i]["model_id"],
                    'modelIcon' => $items[$i]["model_icon"],
                    'ownerId' => $items[$i]["user_id"],
                    'spaceId' => null,
                    'accessControl' => $items[$i]["access"],
                    'moveControl' => $items[$i]["move"],
                    'editControl' => $items[$i]["edit"],
                    'forSale' => $items[$i]["for_sale"],
                    'widgetPersistence' => true,
                    'salePrice' => $items[$i]["price"],
                    'salePriceTokens' => $items[$i]["priceTokens"],
                    'iconPostfix' => $items[$i]["item_icon_postfix"],
                    'success' => true
                ), "model" => array (
                      'id' => $items[$i]["model_id"],
                      'icon' => $items[$i]["model_icon"],
                      'source' => $items[$i]["model_source"],
                      'type' => $items[$i]["model_type"],
                      'tags' => $items[$i]["model_tags"],
                      'desc' => $items[$i]["model_desc"],
                      'accessControl' => $items[$i]["access"],
                      'moveControl' => $items[$i]["move"],
                      'editControl' => $items[$i]["edit"],

                    )  );
                }

                          return $arr;
  }

  function getItem($tag)
    {

        $user = Users::where('id', session('user'))->get();
        $aitems = avatarItems::where('user_id', session('user'));
        $items = avatarItems::where('user_id',  session('user'))->join('items', 'items.model_id', '=', 'avatar_items.model_id')->where('items.model_tags', 'like', '%'.$tag.'%')->get();
        
        //if $items[$i]["model_id"] contains more than one then combine them into one array and record the number of how many there is 

       

        // $c = $user->aitems->items->get();
        $count = count($items);
        $ret = new stdClass();
// $models = items::where('model_id', $items[0]["model_id"])->get();
// echo $models[0]["model_id"];
        $arr = array();
        $k = 1;
     
        $model = items::where('model_tags', 'like','%emote%')->get();
        // $mc = count($model);
            
        for ($i = 0; $i < $count; $i++)
        {

            // $models = avatarItems::where('model_id', $model[$i])->get();            
            // var_dump($model->toArray());
            // var_dump($items);
            // var_dump($count);
            // $items[$i]["model_id"]
            // $result = array_intersect($models, $model->toArray());
        // echo $result;
            // if ($models == $model && $model != null )
            // {
                // var_dump($count);

                $arr[] = array(
                    // 'c' => $c,
                    'count' => $items[$i]["item_count"],
                    'modelPriceTokens' => $items[$i]["model_price_tokens"],
                    'modelClassId' => $items[$i]["model_cid"],
                    'modelTags' =>$items[$i]["model_tags"],
                    'giftedByAvatarLastName' => null,
                    'modelAllowedCount' => 0,
                    'iconPostfix' => $items[$i]["item_icon_postfix"],
                    'lastPurchasedWithTokens' => $items[$i]["item_last_purchased_tokens"],
                    'modelSource' => $items[$i]["model_source"],
                    'modelDetails' => $items[$i]["model_details"],
                    'modelDesc' => $items[$i]["model_desc"],
                    'config' => $items[$i]["item_config"],
                    'giftedByAvatarId' => null,
                    'hasPersistedConfig' => false,
                    'giftWrapModelSource' => $items[$i]["giftWrapModel_source"],
                    'modelMinXPLevel' => $items[$i]["model_min_xplevel"],
                    'modelPriceGold' => $items[$i]["model_price_gold"],
                    'itemId' => $items[$i]["item_id"],
                    'modelId' => $items[$i]["model_id"],
                    'modelIcon' => $items[$i]["model_icon"],
                    'success' => true
                            );
            // }
            // else 
            //     continue;
            
            // var_dump($mc);
            //var_dump($models[0]["model_id"]);
            

        }
    
        return $arr;

    }

  function saveItemIcon($timeconfig, $itemID, $icon)
  {
    $amf = new stdClass();
    $amf->success = true;
    return $amf;
  }

  function isItemInCatalog($timeconfig, $itemID)
  {
    return new BooleanResult(true);
  }

}
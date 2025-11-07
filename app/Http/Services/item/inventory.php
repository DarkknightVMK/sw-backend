<?php
// include_once ('../functions.php');
use App\Models\items;
use App\Models\avatarItems;
use App\Models\Avatars;
use App\Models\User;
use App\Http\Services\functions;

class inventory 
{

    function getInventoryItems($item_id)
    {
        //$item_id is an array of item_id's
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.service.result.ListResult";
        $ret->startIndex = null;
        $ret->maxLength = null;
        $ret->totalCount = 0;

        $ret->list = $this->getInventory($item_id);
        $ret->success= true;
        return $ret;
    }
    function getMyEmotes()
    {
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.service.result.ListResult";


        $ret->list = $this->getEmotes();
        $ret->success= true;
        return $ret;
    }
    function getMySpeechBubbles()
    {
        $ret= new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.service.result.ListResult";

        $ret->list = $this->getBubbles();

        $ret->success = true;
        return $ret;
    }

    function getMyInventory()
    {
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.service.result.TypedRecordSetResult";
        $ret->startIndex = null;
        $ret->maxLength = null;
        $ret->totalCount = 0;

        $ret->recordSet = $this->getInventory();
        $ret->success= true;
        return $ret;
    }

    function getEmotes()
    {

        $user = User::where('id', session('user'))->get();
        $aitems = avatarItems::where('user_id', session('user'));
        $items = avatarItems::where('user_id',  session('user'))->join('items', 'items.model_id', '=', 'avatar_items.model_id')->where('items.model_tags', 'like','%emote%')->get();
        // $model = items::where('model_tags', 'like','%emote%')->get();
        // $modelID = avatarItems::where('model_id', $items[$i]["model_id"])->get();
   
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
                $arr[] = array (
                    // 'c' => $c,
                    'count' => (float)$items[$i]["item_count"],
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
                    'modelMinXPLevel' => (float)$items[$i]["model_min_xplevel"],
                    'modelPriceGold' => (float)$items[$i]["model_price_gold"],
                    'itemId' => $items[$i]["item_id"],
                    'modelId' => (string)$items[$i]["model_id"],
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

    function getBubbles()
    {
        $items = avatarItems::where('user_id', session('user'))->join('items', 'items.model_id', '=', 'avatar_items.model_id')->where('items.model_tags', 'like','%speechbubble%')->get();
        $count = count($items);
        $arr = array();     
        $model = items::where('model_tags', 'like','%emote%')->get();
            
        for ($i = 0; $i < $count; $i++)
        {

                $arr[] = array (
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

        }
        return $arr;
    }

    function getInventory($item_id = null)
    {
        if ($item_id != null) 
            $items = avatarItems::whereIn('item_id', $item_id)->get();
        else
        $items = avatarItems::where('user_id',  session('user'))->join('items', 'items.model_id', '=', 'avatar_items.model_id')->where('space_id','=', null)->get();



        $count = count($items);
        $ret = new stdClass();
// $models = items::where('model_id', $items[0]["model_id"])->get();
// echo $models[0]["model_id"];
        $arr = array();

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
'count' =>(float) $item->item_count,
'modelPriceTokens' => $item->model_price_tokens,
'modelClassId' => $item->model_cid,
'modelTags' =>$item->model_tags,
'giftedByAvatarLastName' => ($item->item_gifted_by_avatar != null) ? functions::getAvatarByID($item->item_gifted_by_avatar, 'lastName') : null,
'giftedByAvatarFirstName' => ($item->item_gifted_by_avatar != null) ? functions::getAvatarByID($item->item_gifted_by_avatar, 'firstName') : null,
'modelAllowedCount' => 0,
'iconPostfix' => ($item->giftWrapModel_id != null) ? items::where('model_id', $item->giftWrapModel_id)->first()->item_icon_postfix : $item->item_icon_postfix,
'lastPurchasedWithTokens' => (bool)$item->item_last_purchased_tokens,
'modelSource' => $item->model_source,
'modelIcon' => ($item->giftWrapModel_id != null) ? items::where('model_id', $item->giftWrapModel_id)->first()->model_icon : $item->model_icon,
'modelDetails' => $item->model_details,
'modelDesc' => $item->model_desc,
'config' => $item->item_config,
'giftedByAvatarId' => $item->item_gifted_by_avatar,
'hasPersistedConfig' => false,
'giftWrapModelSource' => ($item->giftWrapModel_id != null) ? items::where('model_id', $item->giftWrapModel_id)->first()->model_source : null,
'giftWrapModelIcon' => ($item->giftWrapModel_id != null) ? items::where('model_id', $item->giftWrapModel_id)->first()->model_icon : null,
'modelMinXPLevel' => (float)$item->model_min_xplevel,
'modelPriceGold' => (float)$item->model_price_gold,  

'modelIcon' => $item->model_icon,
'itemId' => $item->item_id,
'modelId' => (string)$item->model_id,
'valueTokens' =>  $item->model_price_tokens, // LAST PURCHASED AMOUNT
'giftedByAvatarThumbPostfix' => ($item->item_gifted_by_avatar != null) ? functions::getAvatarByID($item->item_gifted_by_avatar, 'headPostfix') : null,       
'valueGold' =>  (float)$item->model_price_gold,
'lastPurchasedAmount' =>  $item->model_price_gold,
'lastPurchasedTokens' => $item->model_price_tokens,
'success' => true
);

    
}
// var_dump($arr);
    //     for ($i = 0; $i < count($items); $i++)
    //     {


    //         $models = items::where('model_id', $items[$i]["model_id"])->get();
    //                 // if items.model_id is the 
                    
    //         $arr[] = array (
    //         'count' =>(float) $items[$i]["item_count"],
    //         'modelPriceTokens' => $models[0]["model_price_tokens"],
    //         'modelClassId' => $models[0]["model_cid"],
    //         'modelTags' =>$models[0]["model_tags"],
    //         'giftedByAvatarLastName' => ($items[$i]["item_gifted_by_avatar"] != null) ? functions::getAvatarByID($items[$i]["item_gifted_by_avatar"], 'lastName') : null,
    //         'giftedByAvatarFirstName' => ($items[$i]["item_gifted_by_avatar"] != null) ? functions::getAvatarByID($items[$i]["item_gifted_by_avatar"], 'firstName') : null,
    //         'modelAllowedCount' => 0,
    //         'iconPostfix' => ($items[$i]['giftWrapModel_id'] != null) ? items::where('model_id', $items[$i]["giftWrapModel_id"])->first()->item_icon_postfix : $items[$i]["item_icon_postfix"],
    //         'lastPurchasedWithTokens' => (bool)$items[$i]["item_last_purchased_tokens"],
    //         'modelSource' => $models[0]["model_source"],
    //         'modelIcon' => ($items[$i]['giftWrapModel_id'] != null) ? items::where('model_id', $items[$i]["giftWrapModel_id"])->first()->model_icon : $models[0]["model_icon"],
    //         'modelDetails' => $models[0]["model_details"],
    //         'modelDesc' => $models[0]["model_desc"],
    //         'config' => $items[$i]["item_config"],
    //         'giftedByAvatarId' => $items[$i]["item_gifted_by_avatar"],
    //         'hasPersistedConfig' => false,
    //         'giftWrapModelSource' => ($items[$i]['giftWrapModel_id'] != null) ? items::where('model_id', $items[$i]["giftWrapModel_id"])->first()->model_source : null,
    //         'giftWrapModelIcon' => ($items[$i]['giftWrapModel_id'] != null) ? items::where('model_id', $items[$i]["giftWrapModel_id"])->first()->model_icon : null,
    //         'modelMinXPLevel' => (float)$models[0]["model_min_xplevel"],
    //         'modelPriceGold' => (float)$models[0]["model_price_gold"],  

    //          'modelIcon' => $models[0]["model_icon"],
    //         'itemId' => $items[$i]["baseItemId"],
    //         'modelId' => (string)$models[0]["model_id"],
    //         'valueTokens' =>  $models[0]["model_price_tokens"], // LAST PURCHASED AMOUNT
    //         'giftedByAvatarThumbPostfix' => ($items[$i]["item_gifted_by_avatar"] != null) ? functions::getAvatarByID($items[$i]["item_gifted_by_avatar"], 'headPostfix') : null,       
    //         'valueGold' =>  (float)$models[0]["model_price_gold"],
    //         'lastPurchasedAmount' =>  $models[0]["model_price_gold"],
    //         'lastPurchasedTokens' => $models[0]["model_price_tokens"],
    //         'success' => true
    //     );
    //    } 
       return $arr;
        // $ret = new stdClass();
        // $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        // $ret->$explicitTypeField = "com.smallworlds.entity.item.inventory.exernal.amf.result.InventoryItemResult";
        // $ret->count = 1;
        // $ret->test = $count;
        // $ret->modelPriceTokens= 5900;
        // $ret->modelClassId= "com.smallworlds.entity.item.SpriteItem";
        // $ret->modelTags= "consumable,clothing,actionbar,wear,bot03,action_wearable_toggle";
        // $ret->giftedByAvatarLastName = null;
        // $ret->modelAllowedCount=0;
        // $ret->iconPostfix = "";
        // $ret->lastPurchasedWithTokens = true;
        // $ret->modelSource = "items/base/consumables/clothing/bot03/bot03_26_item_jeanshortsturn.xml";
        // $ret->modelDetails="A pair of denim shorts with turned-up cuffs";
        // $ret->modelDesc = "Turn-Up Jean Shorts";
        // $ret->config = "";
        // $ret->giftedByAvatarId = null;
        // $ret->hasPersistedConfig = false;
        // $ret->giftWrapModelSource = null;
        // $ret->modelMinXPLevel = 1;
        // $ret->modelPriceGold=590;
        // $ret->itemId = "e8c54ea00022fd3a7e1191440104dd9a";
        // $ret->modelId = "3190";
        // $ret->modelIcon = "items/base/consumables/clothing/bot03/bot03_26_icon_a.png";
        // $ret->success = true;
        // return $ret;
    }
    // public function getAvatar($json = null)
    // {
    //     $avatar_json = Avatars::where('user_id', session('user'))->get();
    //     $avatar_json = $avatar_json->toJson();
    //     preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_json, $json2decode);
    //     $avatar = json_decode($json2decode[0][0], true);
    //     if ($json == null)
    //         return $avatar;
    //     return $avatar[$json];
    // }
}
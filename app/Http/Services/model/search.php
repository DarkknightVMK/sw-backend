<?php
use App\Models\items;
use App\Models\avatarItems;
use App\Models\Users;
use App\Models\catalogItemEntry;
class search
{
    function getSearchableItems()
    {
        $amf = new stdClass();
        $amf->list = $this->search();
        $amf->success=true;
        return $amf;
    }
    function getAvailabilityInfo($modelID, $arr2)
    {
        $amf = new stdClass();
        $amf->modelAvailability = $this->getAvailable($modelID);
        $amf->catalogBundleAvailability = array();
        // $amf->
        $amf->success=true;
        return $amf;
    }
    function search()
    {
        $items = items::all();
        $count = count($items);

      $ret = new stdClass();
      $arr = array();
      
      for ($i = 0; $i < $count; $i++)
      {
        $arr[] = array (
            'model' => array
            (
                'id' => strval($items[$i]['model_id']),
                'desc' => $items[$i]['model_desc'],
                'tags' => $items[$i]['model_tags'],
                'type' => "item",
                'source' => $items[$i]['model_source'],
                'premiumOnly' => $items[$i]['model_premium_only'],
                'cid' => $items[$i]['model_cid'],
                'details' => $items[$i]['model_details'],
                'priceTokens' => $items[$i]['model_price_tokens'],
                'priceGold' => ($items[$i]['model_price_gold'] == 0 ) ? 10 :$items[$i]['model_price_gold'],
                'icon' => $items[$i]['model_icon'],
                'accessControl' => "A",
                'moveControl' => "C",
                'editControl' => "C",
                'minXPLevel' => $items[$i]['model_min_xplevel'],
                'quantity' => -1,
            ),
            
            // 'catalogBundle' => array
            // (
            //     'isSingleEntry' => false,
            //     // 'expiryDate' => '',
            //     'priceTokens' => $items[$i]['model_price_tokens'],
            //     'isBundleEntry' => true,
            //     'id' => 1,
            //     'name' => $items[$i]['desc'],
            //     'orderIndex' => 1,
            //     // 'totalTime' => 0,
            //     'childEntryNames' => "",
            //     'allowedCount' => 100,
            //     'priceGold' => $items[$i]['model_price_gold'],
            //     'icon' => $items[$i]['model_icon'],
            //     'quantity' => 100,
            //     'modelTags' => $items[$i]['model_tags'],
            //     'modelSource' => $items[$i]['model_source'],
            //     'modelClassId' => $items[$i]['model_cid'],
            //     'success' => true,
            
            // )
        );
      }
        return $arr;
    }

    function getAvailable($modelID)
    {
        $items = items::find($modelID);
        $count = count($items);

      $ret = new stdClass();
      $arr = array();
      
      for ($i = 0; $i < $count; $i++)
      {
        $itemEntry = catalogItemEntry::where('modelId', $items[$i]['model_id'])->get()->first();

        $arr[] = array (
           


            'gold' => ($itemEntry != null) ? (($items[$i]['model_price_gold'] == 0 ) ? 10 :$items[$i]['model_price_gold']) : null,
            'modelId' => $items[$i]['model_id'],
            'tokens' =>($itemEntry != null) ? $items[$i]['model_price_tokens'] : null,
            'type' => ($itemEntry != null) ? 'C' : 'NA-U',
            'saleDiscount' => null,
            'orginalTokens' => null,
            'orginalGold' => null,
            'quantity' => ($itemEntry != null) ? -1 : null,
            'active' => ($itemEntry != null) ? true : null,
            'catalogEntry' => ($itemEntry != null) ? $this->getEntry($items[$i]['model_id']) : null,
            'id' => strval($items[$i]['model_id']),
            
        );
      }
 
        return $arr;
    }



    function getEntry($modelID)
    {
        $itemEntry = catalogItemEntry::where('modelId', $modelID)->get();
        $list = array();

    foreach ($itemEntry as $entry)
    {
      $items = items::where('model_id', $entry->modelId)->first();
    //   $list = array();
      $entry->priceGold =  ($entry->salePriceGold == 0) ? 10 : $entry->salePriceGold;
      $entry->vipOnly = $items->model_premium_only; 
      $entry->startTime = null;
      $entry->stopTime = null;
      $entry->timeLeft = 0;    
      $entry->icon = $items->model_icon;
      $entry->priceTokens = $items->model_price_tokens; 
      // $entry->id = $items->model_id;
      $entry->name = $items->model_desc;
      $entry->id = (string)($entry->id);
      $entry->active = boolval($entry->active);
      $entry->modelClassId = $items->model_cid;
      $entry->modelSource = $items->model_source;
      $entry->modelIcon = $items->model_icon;
      $entry->modelIconUrl = $items->model_icon;
      $entry->modelDesc = $items->model_desc;
      $entry->modelDetails = $items->model_details;
      $entry->modelTags = $items->model_tags;
      $entry->modelPriceGold = $items->model_price_gold;
      $entry->modelPriceTokens = $items->model_price_tokens;
      $entry->modelAllowedCount = 0;
      $entry->modelMinXPLevel = $items->model_min_xplevel;
      $entry->isBundleEntry = false;
      // $entry->isSingleEntry = true;
      $entry->isSingleItemEntry = true;

      $entry->isSingleCPEntry = false;

      $list = $entry->toArray();
    }   
    return $list;
    }
}

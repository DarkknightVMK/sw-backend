<?php
use App\Models\items;
use App\Models\avatarSpaces;
use App\Models\catalogSpaceEntry;
class catalog_core
{
    function getModelCatalogInfo($timeconfig, $modelID)
    {
        $amf = new stdClass();
        $model = items::where('model_id', $modelID)->get()->first();
        $amf->model = array(
            'isItemModel' => true,
            'desc' => $model['model_desc'],
            'id' => $modelID,
            'isSpaceModel' => false,
            'icon' => $model['model_icon'],
//            'currentEntries' => array(
//                'id' => $modelID,
//                'type' => 'item',
//                'icon' => '',
                'categoryID' => 1,
                'categoryName' => 'Furniture',
                'parentCategoryId' => null,
                'parentCategoryName' => null,
                'modelId' => $modelID,
                'modelDesc' => $model['model_desc'],
                'modelIcon' => $model['model_icon'],
                'modelPriceGold' => $model['model_price_gold'],
                'itemId' => '1234',
                'salePriceGold' => '',
                'startDate' => '',
                'endDate' => '',
                'quantity' => null,
                'orderIndex' => -1,
                'active' => true,
                'repeatTime' => 0,
//                'model' => array(
//                    'isItemModel' => true,
//                    'desc' => $model['model_desc'],
//                    'id' => $modelID,
//                    'isSpaceModel' => false,
//                    'icon' => $model['model_icon'],
//                ),
//            ),
        );
//        $amf->currentEntries = array(
//
//            'id' => $modelID,
//            'type' => 'item',
////            'icon' => '',
//            'categoryID' => strval(1),
//            'categoryName' => 'Furniture',
//            'parentCategoryId' => null,
//            'parentCategoryName' => null,
//            'modelId' => $modelID,
//            'modelDesc' => $model['model_desc'],
//            'modelIcon' => $model['model_icon'],
//            'modelPriceGold' => $model['model_price_gold'],
//            'itemId' => '1234',
//            'salePriceGold' => '',
//            'startDate' => '',
//            'endDate' => '',
//            'quantity' => null,
//            'orderIndex' => -1,
//            'active' => true,
//            'repeatTime' => 0,
//        );
        $amf->success=true;
        return $amf;
    }

    function getSpaceCatalogInfo($timeconfig, $spaceID)
    {
        $amf = new stdClass();
        $amf->id = $spaceID;
        $amf->space = $this->spaceSMIDetails($spaceID);
        $amf->currentEntries = $this->currentEntries($spaceID);
        // $amf->active = true;
        $amf->success=true;
        return $amf;
    }


    function spaceSMIDetails($space_id)
    {
        $space = avatarSpaces::where('id', $space_id)->first();
        return array(
              "id" => $space['id'],
              "spaceOwner" => $space["avatar_id"],
              "modelSale" => ($space["forSale"] == 'Y') ? true : false,
              "salePriceGold" => $space["salePriceGold"],
              "salePriceTokens" => $space["salePriceTokens"],
              "instanceSale" => ($space["forSale"] == 'I') ? true : false,
              'avatarDetails' => null,
              'desc' => $space["name"],
              'details' => $space["desc"],
              'type' => $space["type"],
              'iconUrl' => $space["icon"],
              'accessControl' => $space["accessControl"],
              'modelId' => strval($space["modelId"]),
              'spaceRoleAccess' => "0",
              'currentVisitors' => $space['currentVisitors'],
              "instanceId" => '',
              "thumbnailUrl" => $space["spaceThumbnailSource"],
              "snapshotUrl" => $space["spaceSnapshotSource"],
              "active" => boolval($space["active"]),
            );
    }

    function currentEntries($space_id)
    {

        // DATES new Amfphp_Core_Amf_Types_Date(Carbon\Carbon::now()->valueOf()
        $entries = catalogSpaceEntry::where('spaceId', $space_id)->get();
        $avatarSpace = avatarSpaces::where('id', $space_id)->first();
        if (catalogSpaceEntry::where('spaceId', $space_id)->exists()) 
        {
        foreach ($entries as $entry) {

            $arr[] = array (
                'id' => $entry['id'],
                'categoryId' => $entry['categoryId'],
                'categoryName' => $entry['categoryName'],
                'parentCategoryId' => $entry['parentCategoryId'],
                'parentCategoryName' => $entry['parentCategoryName'],
                'orderIndex' => $entry['orderIndex'],
                'spaceId' => $entry['spaceId'],
                'active' => $avatarSpace->active,
                'repeatTime' => $entry['repeatTime'],
                'startDate' => new Amfphp_Core_Amf_Types_Date(Carbon\Carbon::now()->valueOf()),
                'space' => $this->spaceSMIDetails($entry['spaceId']),
            );
            // $entry->model = $this->getModelCatalogInfo(null, $entry->modelId);
        }
        return $arr;
        }
        else
        {
            return $arr[0] = array(

            );
        }
    }

    // 'id' => $value['id'],
    //             'categoryId' => $value['categoryId'],
    //             'categoryName' => $category['name'],
    //             'parentCategoryId' => $value['parentCategoryId'],
    //             'parentCategoryName' => $value['parentCategoryName'],
    //             'orderIndex' => $value['order_index'],
    //             'spaceId' => $value['spaceId'],
    //             'spaceBlocked' => $value['spaceBlocked'],
    //             'startDate' => $value['start_date'],
    //             'endDate' => $value['end_date'],
    //             'repeatTime' => $value['repeat_time'],
    //             $this->spaceSMIDetails($value['spaceId']),
    //             // 'quantity' => $value['quantity'],
    //             'active' => $value['active']
}

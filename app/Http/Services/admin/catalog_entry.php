<?php

use App\Models\avatarSpaces;
use App\Models\catalogCategories;
use App\Models\catalogSpaceEntry;
use App\Models\Users;
use App\Models\Avatars;
use Carbon\Carbon;
use App\Models\catalogItemEntry;
use App\Models\items;
use App\Models\onlineUsers;

class catalog_entry
{   
    
    /**
     * SPACE SMI DETAILS 
     *
     * MOVE TO different class
     */     
     
    
    function spaceSMIDetails($space_id)
    {
        $space = avatarSpaces::where('id', $space_id)->first();

        return array(
              'id' => $space->id,
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
              "instanceId" => '',
              "thumbnailUrl" => $space["spaceThumbnailSource"],
              "snapshotUrl" => $space["spaceSnapshotSource"],
              "active" => boolval($space["active"]),
            );
    }
    function getUserDetails($spaceID)
    {
        $avatar = avatarSpaces::where('id', $spaceID)->pluck('user_id')->first();
        $user = Users::where('id', $avatar)->first();
        return array(
        'id' => strval($user["id"]),
                    'firstName' => $user["firstName"],
                    'lastName' => $user["lastName"],
                    'emailAddress' => $user["email"],
                    'payingUser' => true,
                    //   'warningMessage' => "",
                    'buttonColor' => "",
                    'buttonReason' => ""
        );
    }

    function modelSMIInfo($model_id)
    {
        $model = items::where('model_id', $model_id)->first();
        return array(
            'id' => strval($model["model_id"]),
            // 'name' => $model["name"],
            'isItemModel' => true,
            'isSpaceModel' => false,
            'desc' => $model["model_desc"],
            // 'type' => $model["type"],
            'icon' => strval($model["model_icon"]),
            // 'accessControl' => $model["accessControl"],
            // 'modelId' => strval($model["modelId"]),
            // 'spaceRoleAccess' => "0",
            // 'currentVisitors' => $model['currentVisitors'],
            // "instanceId" => '',
            // "thumbnailUrl" => $model["thumbnailSource"],
            // "snapshotUrl" => $model["snapshotSource"],
            // "active" => boolval($model["active"]),
        );
    }
    

    
    function getItemEntriesForCategory($timeconfig, $catID, $active)
    {
        $category = catalogCategories::where('id', $catID)->first();
        $itemEntry = catalogItemEntry::where('categoryId', $catID)->get();
       
        // $model = items::where('model_id', $itemEntry->modelId)->first();
        $arr = array();

        $amf = new stdClass();
        if (catalogItemEntry::where('categoryId', $catID)->exists())
        {
            foreach($itemEntry as $entry)
            {
                // $modelID= $itemEntry;   
        // var_dump($model);             

               
                $entry->model = $this->modelSMIInfo($entry->modelId);
                // $entry->add('{"modelDesc": '. $model->model_desc.', "modelPriceGold": '. $model->model_price_gold.', "modelIcon": '. $model->model_icon.'}');
                // $entry->modelPriceGold = $model->model_price_gold;
                // $entry->modelIcon = $model->model_icon;
                $entry->active = boolval($entry->active);

                $amf->recordSet[] = $entry->toArray();
            }
        }
        $amf->success=true;
        return $amf;
    }

    function getSpaceEntriesForCategory($timeconfig, $catId, $isActive)
    {   
        $category = catalogCategories::where('id', $catId)->first();
        if (!$isActive)
        {
            // show only active
            $entry = catalogSpaceEntry::where('categoryId', $catId)->where('active', $isActive)->get()->sortBy('orderIndex');
        }
        $entry = catalogSpaceEntry::where('categoryId', $catId)->get();
        $amf = new stdClass();
        $arr = array();
        // var_dump( catalogSpaceEntry::where('categoryId', $catId)->exists());
 
        if (catalogSpaceEntry::where('categoryId', $catId)->exists() )
        {
            // $catalogCategory = catalogCategories::where('id', $catId)->get()->first();

            // $mytime = strtotime($mytime);
            // var_dump($mytime);
        foreach ($entry as $key => $value) {
            $avatarSpace = avatarSpaces::where('id', $value->spaceId)->first();
            
                // ($catalogCategory != null) ? $parentCat = catalogCategories::where('id', $catalogCategory->parentId)->first() : $parentCat = null;
            $currentVisitors = onlineUsers::where('space_id', $value['spaceId'])->where('online',true)->count();

            $arr[] = array(
                // if ($isActive == true && $value['active'] == true)
                // {};
                'id' => strval($value['id']),
                'categoryId' => $value['categoryId'],
                'categoryName' => $value['categoryName'],
                // 'parentCategoryId' => ($parentCat->id != null) ? $parentCat->id : null,
                // 'parentCategoryName' => ($parentCat->name != null) ? $parentCat->name : null,
                'orderIndex' => $value['orderIndex'],
                'spaceId' => $value['spaceId'],
                'numUsers' => $currentVisitors,
                'spaceBlocked' => boolval($value['spaceBlocked']),
                'startDate' => $value['startDate'],
                'endDate' => $value['endDate'],
                'repeatTime' => $value['repeaTime'],
                'space' => $this->spaceSMIDetails($value['spaceId']),
                'ownerUser' => $this->getUserDetails($value['spaceId']),
                // 'quantity' => $value['quantity'],
                'active' => boolval($avatarSpace->active)
            );
            
        }
    }
        $amf->recordSet = $arr;
        $amf->success=true;
    return $amf;
    }
    function manageSpaceEntries($timeconfig, $add, $edit, $delete)
    {
        $amf = new stdClass();
        // 
        $add2 = str_replace('["', '', $add);
            $add3 = str_replace('"]', '', $add2);
            $edit1 = str_replace('["', '', $edit);
            $edit2 = str_replace('"]', '', $edit1);
            $delete1 = str_replace('["', '', $delete);
            $delete2 = str_replace('"]', '', $delete1);
            $add= json_decode($add3, true);
            $edit = json_decode($edit2, true);
            $delete = json_decode($delete, true);
            // ADD
            foreach ($add as $key => $value) 
            {
                catalogSpaceEntry::create(
                    [
                        'categoryId' => $value['categoryId'],
                        'categoryName' => $value['categoryName'],
                        'parentCategoryId' => $value['parentCategoryId'],
                        'parentCategoryName' => $value['parentCategoryName'],
                        'orderIndex' => $value['orderIndex'],
                        'spaceId' => $value['spaceId'],
                        'spaceBlocked' => $value['spaceBlocked'],
                        'startDate' => $value['startDate'],
                        'endDate' => $value['endDate'],
                        'repeatTime' => $value['repeatTime'],
                        'active' => true
                    ]
                    
                );
            }
            // EDIT
            foreach ($edit as $key => $value) 
            {
                catalogSpaceEntry::where('id', $value['id'])->update(
                    [
                        'categoryId' => $value['categoryId'],
                        'categoryName' => $value['categoryName'],
                        'parentCategoryId' => $value['parentCategoryId'],
                        'parentCategoryName' => $value['parentCategoryName'],
                        'orderIndex' => $value['orderIndex'],
                        'spaceId' => $value['spaceId'],
                        'spaceBlocked' => $value['spaceBlocked'],
                        'startDate' => null,
                        'endDate' => $value['endDate'],
                        'repeatTime' => $value['repeatTime'],
                        'active' => $value['active']
                    ]
                );
            }
            // DELETE
            foreach ($delete as $key => $value) 
            {
                catalogSpaceEntry::where('id', $value['id'])->delete();
            }

        $amf->success=true;
        return $amf;
    }

    function deleteEntry($timeconfig, $entryId)
    {
        $amf = new stdClass();
        catalogSpaceEntry::where('id', $entryId)->delete();
        $amf->success=true;
        return $amf;
    }

    function updateEntryOrder($data)
    {
        //reorder data
        // $data = implode( $data);      
        // $data = explode(" ",$data);
        $data = array_filter($data);   
        $data = array_map('intval', $data);
        //   var_dump($data);
        
        $order = 0;
        foreach ($data as $id) {
            $order++;
            $catId = catalogSpaceEntry::where('id', $id)->get()->first();
            $catId->orderIndex = $order;
            $catId->save();
        }
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

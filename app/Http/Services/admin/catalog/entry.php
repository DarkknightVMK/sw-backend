<?php

use App\Models\avatarSpaces;
use App\Models\catalogCategories;
use App\Models\catalogItemEntry;
use App\Models\Users;
use App\Models\Avatars;
use App\Models\items;
use Carbon\Carbon;

class entry
{

    function addCPEntry($timeconfig, $amountCP, $icon, $catID, $scheduleStart, $scheduleEnd, $goldPrice)
    {
        $cat = catalogCategories::where('id', $catID)->first();
        $cpEntry = catalogItemEntry::where('type', 'C')->first();
        $item_category = catalogItemEntry::create(
            [
                'categoryId' => $cat->id,
                'categoryName' => $cat->name,
                'parentCategoryId' => $cat->parentId,
                'parentCategoryName' => $cat->parentName,
                'cp' => $amountCP,
                'orderIndex' => $cpEntry != null ? $cpEntry->orderIndex + 1 : 1,
                'shareDiscount' => null,
                'discounted' => false,           
                'icon' => $icon,             
                'startDate' => $scheduleStart,
                'endDate' => $scheduleEnd,
                'repeatTime' => null,
                'quantity' => 1,
                'type' => 'C',
                'salePriceGold' => $goldPrice,
                'active' => true
            ]
            
        );
        catalogItemEntry::where('id', $item_category->id)->update(['orderIndex' => $item_category->id]);
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }

    function editCPEntry($timeconfig, $entryID, $amountCP, $icon, $catID, $scheduleStart, $scheduleEnd, $goldPrice)
    {
        $cat = catalogCategories::where('id', $catID)->first();
        $cpEntry = catalogItemEntry::where('id', $entryID)->first();
        $item_category = catalogItemEntry::where('id', $entryID)->update(
            [
                'categoryId' => $cat->id,
                'categoryName' => $cat->name,
                'parentCategoryId' => $cat->parentId,
                'parentCategoryName' => $cat->parentName,
                'cp' => $amountCP,
                'orderIndex' => $cpEntry->orderIndex,
                'shareDiscount' => null,
                'discounted' => false,           
                'icon' => $icon,             
                'startDate' => $scheduleStart,
                'endDate' => $scheduleEnd,
                'repeatTime' => null,
                'quantity' => 1,
                'type' => 'C',
                'salePriceGold' => $goldPrice,
                'active' => true
            ]
            
        );
        // catalogItemEntry::where('id', $item_category->id)->update(['orderIndex' => $item_category->id]);
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
    
    function getEntriesForCategory($timeconfig, $catID)
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
        $model = items::where('model_id', $entry->modelId)->get();                
        // var_dump($model);          
            if ($entry->type == 'C')   
                $entry->modelId = $entry->cp;

                foreach ($model as $models)
                {
                    $entry->modelDesc = $models->model_desc;
                    $entry->modelPriceGold = $models->model_price_gold;
                    $entry->modelIcon = $models->model_icon;
                }
                // $entry->add('{"modelDesc": '. $model->model_desc.', "modelPriceGold": '. $model->model_price_gold.', "modelIcon": '. $model->model_icon.'}');
                // $entry->modelPriceGold = $model->model_price_gold;
                // $entry->modelIcon = $model->model_icon;
                $amf->recordSet[] = $entry->toArray();
            }
        }
        // $amf->recordSet = array(
        //     'id' => 1,
        //     'type' => 'standard',
        //     'icon' => '',
        //     'categoryID' => 1,
        //     'categoryName' => 'Furniture',
        //     'parentCategoryId' => 1,
        //     'parentCategoryName' => null,
        //     'modelId' => '',
        //     'modelDesc' => '',
        //     'modelIcon' => '',
        //     'modelPriceGold' => '',
        //     'itemId' => '1234',
        //     'salePriceGold' => '',
        //     'startDate' => '',
        //     'endDate' => '',
        //     'quantity' => null,
        //     'orderIndex' => -1,
        //     'active' => true
        // );
        $amf->success=true;
        return $amf;
    }

    function getEntriesForModel($timeconfig, $modelID)
    { // use entry result
        $model = catalogItemEntry::where('modelId', $modelID)->get();
        $modelInfo = items::where('model_id', $modelID)->first();
        $amf = new stdClass();
        if (catalogItemEntry::where('modelId', $modelID)->exists())
        {
            foreach($model as $entry)
            {
                $entry->modelPriceGold = $modelInfo->price_gold;
                $entry->modelIcon = $modelInfo->icon;
                $entry->modelDesc = $modelInfo->description;
                $amf->recordSet[] = $entry->toArray();
            }
        }
        else
        {
            $amf->recordSet = array(
                'id' => $modelInfo->model_id,
                'modelId' => $modelInfo->model_id,
                'modelDesc' => $modelInfo->model_desc,
                'modelIcon' => $modelInfo->model_icon,
                'modelPriceGold' => strval($modelInfo->model_price_gold),
                'salePriceGold' => strval($modelInfo->model_price_gold),
                'shareDiscount' => 0,
                'startDate' => null,
                'active' => true,
                // 'categoryName' => null,
                // 'categoryId' => null,
            // 'orderIndex' => 1000,
            // 'discounted' => 0,
                'type' => 'standard',
            
                
            );
        }

        // $amf->recordSet = array(
        //     'id' => 1,
        //     'type' => 'standard',
        //     'icon' => '',
        //     'categoryID' => 1,
        //     'categoryName' => 'Furniture',
        //     'parentCategoryId' => 1,
        //     'parentCategoryName' => null,
        //     'modelId' => $model->model_id,
        //     'modelDesc' => $model->model_desc,
        //     'modelIcon' => $model->model_icon,
        //     'modelPriceGold' => $model->model_price_gold,
        //     'itemId' => '',
        //     'salePriceGold' => '',
        //     'startDate' => '',
        //     'endDate' => '',
        //     'quantity' => null,
        //     'orderIndex' => -1,
        //     'active' => true
        // );
        $amf->success=true;
        return $amf;
    }

    function manageEntries($timeconfig, $add, $edit, $delete)
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
            //get the last orderIndex of the category
            foreach ($add as $key => $value) 
            {
                $lastOrderIndex = catalogItemEntry::where('categoryId', $value['categoryId'])->max('orderIndex');

                if ($value['discounted'] == 'true')
                {
                    $initprice = $value['priceGold'];
                    $price = $value['priceGold'] * ($value['shareDiscount'] * .01);
                    $price = $initprice - $price;
                }
                else
                {
                    $price = $value['priceGold'];
                }
                catalogItemEntry::create(
                    [
                        'categoryId' => $value['categoryId'],
                        'categoryName' => $value['categoryName'],
                        'parentCategoryId' => $value['parentCategoryId'],
                        'parentCategoryName' => $value['parentCategoryName'],
                        'modelId' => $value['modelId'],
                        // 'orderIndex' => $lastOrderIndex++, // Last orderIndex + 1
                        'orderIndex' => $value['orderIndex'],
                        'shareDiscount' => $value['shareDiscount'],
                        'discounted' => $value['discounted'],                        
                        'startDate' => $value['startDate'],
                        'endDate' => $value['endDate'],
                        'repeatTime' => $value['repeatTime'],
                        'quantity' => $value['quantity'],
                        'type' => 'standard',
                        'salePriceGold' => $price,
                        'active' => true
                    ]
                    
                );
            }
            // EDIT
            foreach ($edit as $key => $value) 
            {
                if ($value['discounted'] == 'true')
                {
                    $initprice = $value['priceGold'];
                    $price = $value['priceGold'] * ($value['shareDiscount'] * .01);
                    $price = $initprice - $price;
                }
                else
                {
                    $price = $value['priceGold'];
                }
                catalogItemEntry::where('id', $value['id'])->update(
                    [
                        'categoryId' => $value['categoryId'],
                        'categoryName' => $value['categoryName'],
                        'parentCategoryId' => $value['parentCategoryId'],
                        'parentCategoryName' => $value['parentCategoryName'],
                        'orderIndex' => $value['orderIndex'],
                        'shareDiscount' => $value['shareDiscount'],
                        'discounted' => $value['discounted'],                        
                        'startDate' => $value['startDate'],
                        'endDate' => $value['endDate'],
                        'repeatTime' => $value['repeatTime'],
                        'quantity' => $value['quantity'],
                        'type' => 'standard',
                        'salePriceGold' => $price,
                        'active' => true
                    ]
                );
            }
            // DELETE
            foreach ($delete as $key => $value) 
            {
                catalogItemEntry::where('id', $value['id'])->delete();
            }

        $amf->success=true;
        return $amf;
    }

    function deleteEntry($timeconfig, $entryId)
    {
        // get user permissions
        $user = Auth::user();
        $amf = new stdClass();
        if ($user->primaryGroupId == 1)
        {
        catalogItemEntry::where('id', $entryId)->delete();
        $amf->success=true;
        }
        else
        {
            $amf->success=false;
        }
        return $amf;
    }
}

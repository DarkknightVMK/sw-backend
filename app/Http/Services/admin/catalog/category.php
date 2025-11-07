<?php
use App\Models\catalogCategories;
use App\Models\catalogItemEntry;

class category
{
    function getAllCategories()
    {
        $amf = new stdClass();
        $amf->recordSet = $this->getCat();
        $amf->success=true;
        return $amf;
    }

    function getCategories()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.ListResult";
        $categories = catalogCategories::where('type', 'item')->get()->sortBy('orderIndex');
        foreach ($categories as $category) 
        {
            if ($category->children != null && $category->parentId == null)
            { //inside a category that has children
                $catIds = explode(",", strval($category->children));
                // array(2) {
                //     [0]=>
                //     string(1) "3"
                //     [1]=>
                //     string(1) "7"
                //   }`
                $catIds = Arr::sort($catIds, function($value) {
                    return catalogCategories::where('id', $value)->get()->first()->orderIndex;
                });
                                // array merge catIds with itself
                // $catIds = array_merge($catIds, $catIds);
                // $catIds = array_keys($catIds);
                // $catIds = array_map('intval', $catIds);
                // $catIds = array_unique($catIds);
                // $catIds = array_values($catIds);
    
                // var_dump($catIds);
                $children = array();
                foreach ($catIds as $catId) 
                {
                    $cat = catalogCategories::where('id', $catId)->get()->sortBy('orderIndex');
                    // var_dump(count($cat));
                    

                    foreach ($cat as $cate)
                    {
                        //only returns 1 category :/
                        //  $ret[] = array();
                        // $category->children = ($cat->toArray());
                        $children[] = $cate->toArray();
                        //sort $children by orderIndex
                        // $array = collect($children)->sortBy('orderIndex')->reverse()->toArray();

                        // $children = array_multisort($category->orderIndex, SORT_DESC, $children);

                        // $children = $children->sortBy('orderIndex');
                       

                        $category->children = $children;

                      
                        // $amf->list[] = $cate->toArray();
                    }
                                


                } 
                    
            // $category->children->toArray();
            }
            if ($category->children == null)
            {
                $category->children = array();
            }

            if ($category->parentId != null)
            { //This is a child category
                continue;
            }
            // var_dump($catIds);
            // if ($category->parentId != null)
            //             break;
            $category->active = boolval($category->active);
            $category->sortsByOrderIndex = true;
            $category->isManuallyControlled = boolval($category->isManuallyControlled);
            $category->hasSeparator = boolval($category->hasSeparator);

            $amf->recordSet[] = $category->toArray();
                
        }



        // $amf->recordSet = $this->getCat();
        $amf->success=true;
        return $amf;
    }
    function updateCategoryItemOrder($data)
    {
        //reorder data
        $data = implode( $data);      
        $data = explode("entry_", $data);
        $data = array_filter($data);   
        //   var_dump($data);
        $order = 0;
        foreach ($data as $id) {
            $order++;
            $catId = catalogItemEntry::where('id', $id)->first();
            $catId->orderIndex = $order;
            $catId->save();
        }
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
    function updateCategoryOrder($data)
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
            $catId = catalogCategories::where('id', $id)->get()->first();
            // if ($catId->parentId != null)
            // {
            //     //
            // }
            $catId->orderIndex = $order;
            $catId->save();
        }
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }



    function addCategory($timeconfig, $parentID, $catName, $iconPath, $hasSeparator, $active)
    {
        $item_category = catalogCategories::create
        (
            [
                'type' => 'item',
                'name' => $catName,
                'iconUrl' => $iconPath,
                'hasSeparator' => $hasSeparator,
                'active' => $active,
            ]
        );
        catalogCategories::where('id', $item_category->id)->update(['orderIndex' => $item_category->id]);
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
    function editCategory($timeconfig, $catID, $parentID, $catName, $iconPath, $hasSeparator, $active)
    {
        $amf = new stdClass();

        $cat = catalogCategories::where('id', $catID)->get()->first();
        // if($parentID != $cat->parentId)
        // {
        //     //remove from old parent
        //     $parentCat = catalogCategories::where('id', $parentID)->get()->first();

        //     if ($parentCat != null && !str_contains(strval($parentCat->children), strval($catID)))
        //     {
        //         // append to parent 
        //         // $parentCat->children = $parentID . "," . $parentCat->children;
        //         // $parentCat->children = str_replace($parentID.",", "", $parentCat->children);
        //         $catId = array($parentCat->children, $catID);
        //         if ($parentCat->children != null) 
        //         {
        //             $parentCat->children = implode(",", $catId);
        //         }
        //         else 
        //         {
        //             $parentCat->children = $catID;
        //         }
        //         $parentCat->name;
        //         $parentCat->save();
        //     }
        //     // else if (strpos($parentCat->children , $parentID) == 0 && strpos($parentCat->children, ",") == false)
        //     //     $parentCat->children = str_replace($parentID, "", $parentCat->children);
        //     // else
        //     //     $parentCat->children = str_replace(",".$parentID, "", $parentCat->children);

        //     // $parentCat->save();
        //     $childCat = catalogCategories::where('id', $parentID)->get()->first();


        //     if ($childCat->children == null || $childCat->children == "")
        //     {
        //         $childCat->children = $parentID;
        //     }
        //     else
        //     {
        //         $childCat->children = $childCat->children . "," . $parentID;
        //         //remove 
        //     }

        //     // $parentCat->children = str_replace($catID, "", $parentCat->children);
        //     // $parentCat->save();

        //     $cat->parentId = $parentID;
        //     // //add to child
        //     // $childCat->children = $childCat->children . "," . $catID;
        //     $childCat->save();
        //     $amf->success=true;
        // return $amf;
        // }
        if ($parentID != null )    
        {
            $parentCat = catalogCategories::where('id', $parentID)->get()->first();
            if ($parentCat != null && !str_contains(strval($parentCat->children), strval($catID))) 
            {
                $catId = array($parentCat->children, $catID);
                if ($parentCat->children != null) 
                {
                    $parentCat->children = implode(",", $catId);
                }
                else 
                {
                    $parentCat->children = $catID;
                }
                $parentCat->name;
                $parentCat->save();

                // $parentCat->update(['children' => $catID]);
                
            }
            $parentName = $parentCat->name;
            // $parentCat->save();
        if ($parentCat == null)
            $parentName = null;
        }
        $parentName = null;
        catalogCategories::where('id', $catID)->update
        (
            [
                'type' => 'item',
                'parentID' => $parentID,
                'parentName' => $parentName,
                'name' => $catName,
                'iconUrl' => $iconPath,
                'hasSeparator' => $hasSeparator,
                'active' => $active,
            ]
        );
        // catalogCategories::where('id', $item_category->id)->update(['orderIndex' => $item_category->id]);
        $amf->success=true;
        return $amf;
    }

    function getCat()
    {
        $cat = catalogCategories::where('type', 'item')->get();
        $count = count($cat);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $arr[] = array
            (
                'id' => $cat[$i]["id"],
                'active' => boolval($cat[$i]['active']),
                'isManuallyControlled' => $cat[$i]['isManuallyControlled'],
                'name' => $cat[$i]['name'],
                'sortsByOrderIndex' => boolval($cat[$i]['sortsByOrderIndex']),
                'sortsAlphabetically' => boolval($cat[$i]['sortsAlphabetically']),
                'orderIndex' => $cat[$i]['orderIndex'],
                'type' => $cat[$i]['type'],
                'iconUrl' => $cat[$i]['iconUrl'],
                'isControlledByPopularity' => boolval($cat[$i]['isControlledByPopularity']),
                'hasSeparator' => $cat[$i]['hasSeparator'],
                // 'children' => $cat[$i]['children'],
                'parentId' => $cat[$i]['parentId'],
                'parentName' => $cat[$i]['parentName'],
            );
        }
        return $arr;
    }
}

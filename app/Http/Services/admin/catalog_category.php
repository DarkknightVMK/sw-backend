<?php
use App\Models\catalogCategories;
use App\Models\catalogSpaceEntry;

class catalog_category
{
  function getManualSpaceCategories()
  {
    $type = 'space';
    $amf = new stdClass();
    $amf->recordSet = $this->getCat();
    $amf->success=true;
    return $amf;
  }

  function deleteCategory($timeconfig, $catID)
  {    
    $amf = new stdClass();
    $cat = catalogCategories::where('id', $catID)->get()->first();
    
    $catData = catalogSpaceEntry::where('categoryId', $catID)->get();
    if ($cat != null && $cat->children == null)
    {
     //delete all catData
     if ($catData != null)
     {
        foreach ($catData as $catD)
        {
            $catD->delete();
        }
    }
        // then delete the cat
      $cat->delete();
      $amf->success=true;
      return $amf;

    }
    else
    {
        $catIds = explode(",", strval($cat->children));

        $children = array();
        foreach ($catIds as $catId) 
        {
            $child = catalogCategories::where('id', $catId)->get()->first();
            // convert to parent
            $child->parentId = null;
            $child->active = false;
            $child->orderIndex--;
            $child->save();
        //    var_dump($catId);
        }
        // $cat->children = null;
        if ($catData != null)
        {
            foreach ($catData as $catD)
            {
                $catD->delete();
            }
        }
        // // then delete the cat
        $cat->delete();

        $amf->success=true;
        return $amf;
    }
    $amf->success=false;
    return $amf;
  }

    function addItemCategory($timeconfig, $unk, $name, $icon, $content_manual, $content_pop, $sorting_manual, $sorting_alpha, $has_separator, $active)
    {
        $type = 'item';
//        if (filter_var($icon, FILTER_VALIDATE_URL) === FALSE) {
//            $amf = new stdClass();
//            $amf->success = false;
//            return $amf;
//        }
        $item_category = catalogCategories::create
        (
            [
                'type' => $type,
                'name' => $name,
                'iconUrl' => $icon,
                'isManuallyControlled' => $content_manual,
                'isControlledByPopularity' => $content_pop,
                'sortsByOrderIndex' => $sorting_manual,
                'sortsAlphabetically' => $sorting_alpha,
                'hasSeparator' => $has_separator,
                'active' => $active,
            ]
        );
        catalogCategories::where('id', $item_category->id)->update(['orderIndex' => $item_category->id]);

        $amf = new stdClass();
        $amf->category = $item_category;
        $amf->success=true;
        return $amf;
    }

    function addSpaceCategory($timeconfig, $parentName, $name, $icon, $content_manual, $content_pop, $sorting_manual, $sorting_alpha, $has_separator, $active)
    {
        $type = 'space';
//        if (filter_var($icon, FILTER_VALIDATE_URL) === FALSE) {
//            $amf = new stdClass();
//            $amf->success = false;
//            return $amf;
//        }
        $space_category = catalogCategories::create
        (
            [
                'type' => $type,
                'name' => $name,
                'iconUrl' => $icon,
                'isManuallyControlled' => $content_manual,
                'isControlledByPopularity' => $content_pop,
                'sortsByOrderIndex' => $sorting_manual,
                'sortsAlphabetically' => $sorting_alpha,
                'hasSeparator' => $has_separator,
                'active' => $active,
            ]
        );
        catalogCategories::where('id', $space_category->id)->update(['orderIndex' => $space_category->id]);

        $amf = new stdClass();
        $amf->category = $space_category;
        $amf->success=true;
        return $amf;
    }

    // function updateCategory($timeconfig, $unk, $id, $name, $icon, $content_manual, $content_pop, $sorting_manual, $sorting_alpha, $has_separator, $active)
    // {
    //     $type = 'item';

  function getItemCategoryTree($unk)
  {
    $amf = new stdClass();
    $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
    $amf->$explicitTypeField = "com.smallworlds.service.result.ListResult";
    $categories = catalogCategories::where('type', 'item')->get();
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
            //   }
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
                    $category->children = $children;
                  
                    // $amf->list[] = $cate->toArray();
                }
                            


            } 
                
        // $category->children->toArray();
        }

        if ($category->parentId != null)
        { //This is a child category
            continue;
        }
        // var_dump($catIds);
        // if ($category->parentId != null)
        //             break;
        $category->active = boolval($category->active);
        $category->sortsByOrderIndex = boolval($category->sortsByOrderIndex);
        $category->isManuallyControlled = boolval($category->isManuallyControlled);
        $amf->recordSet[] = $category->toArray();
            
    }



    // $amf->recordSet = $this->getCat();
    $amf->success=true;
    return $amf;
  }

  function editCategory($timeconfig, $catID, $parentID, $catName,$iconPath, $content_manual, $content_auto, $sorting_man,$sorting_alpha,  $hasSeparator, $active)
  {
      if ($parentID != null) 
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
              'type' => 'space',
              'parentID' => $parentID,
              'parentName' => $parentName,
              'name' => $catName,
              'iconUrl' => $iconPath,
              'isManuallyControlled' => $content_manual,
              'isControlledByPopularity' => $content_auto,
              'sortsByOrderIndex' => $sorting_man,
              'sortsAlphabetically' => $sorting_alpha,
              'hasSeparator' => $hasSeparator,
              'active' => $active,
          ]
      );
      // catalogCategories::where('id', $item_category->id)->update(['orderIndex' => $item_category->id]);
      $amf = new stdClass();
      $amf->success=true;
      return $amf;
  }



  function updateItemCategoryOrder($data)
    {
        //reorder data
        $data = array_filter($data);   
        $data = array_map('intval', $data);
        //   var_dump($data);
        $order = 0;
        foreach ($data as $id) {
            $order++;
            $catId = catalogCategories::where('id', $id)->get()->first();
            $catId->orderIndex = $order;
            $catId->save();
        }
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }

  function getSpaceCategoryTree($unk)
  {
      $type = 'space';
      $amf = new stdClass();
      $amf->recordSet = $this->searchCat($type);
      $amf->success=true;
      return $amf;
  }

  function getManualItemCategories(){
      $amf = new stdClass();
      $amf->success=true;
      return $amf;
  }
  function getCat()
    {
        $cat = catalogCategories::where('type', 'space')->get();
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

    function searchCat($type)
    {
        // $user = new functions();
        // if ($aid == null)
        //     $aid = $uid;

        // $cat = catalogCategories::where('type', $type )->get();
//        $avatar = Avatars::where('avatar_id', $userID)->get();
        $ret = new stdClass();
        // $count = count($cat);
        //loop($count, $spaces);
        // var_dump($count);
        $arr = array();

        $categories = catalogCategories::where('type', $type)->get()->sortBy('orderIndex');
        foreach ($categories as $category) 
        {
            if ($category->children != null && $category->parentId == null)
            { //inside a category that has children
                $catIds = explode(",", strval($category->children));
                // sort catIds by orderIndex
                $catIds = Arr::sort($catIds, function($value) {
                    return catalogCategories::where('id', $value)->get()->first()->orderIndex;
                });
                // var_dump($catIds);

                

                // array(2) {
                //     [0]=>
                //     string(1) "3"
                //     [1]=>
                //     string(1) "7"
                //   }`
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
                    $cat = catalogCategories::where('id', $catId)->get();
                    // var_dump($catId);
                    

                    foreach ($cat as $cate)
                    {
                        //only returns 1 category :/
                        //  $ret[] = array();
                        // var_dump($cate->toArray());
                        // $category->children = ($cat->toArray());
                        // $cate = $cate->toArray();
                        $cate->parentName = $category->name;
                        // $arr[] = $cate->toArray();
                        $cate->id = strval($cate->id);

                        // $children[] = $cate->toArray();
                        $children[] = array(
                            'id' => strval($cate->id),
                            'name' => $cate->name,
                            'parentName' => $category->name,
                            'parentId' => strval($cate->parentId),
                            'iconUrl' => $cate->iconUrl,
                            'isManuallyControlled' => (bool)$cate->isManuallyControlled,
                            'isControlledByPopularity' => (bool)$cate->isControlledByPopularity,
                            'sortsByOrderIndex' => (bool)$cate->sortsByOrderIndex,
                            'sortsAlphabetically' => (bool)$cate->sortsAlphabetically,
                            'hasSeparator' => (bool)$cate->hasSeparator,
                            'active' => (bool)$cate->active,
                            'orderIndex' => (float)$cate->orderIndex,
                            'type' => $cate->type,
                            // 'children' => null,
                        );
                            
                        // $children->id = strval($cate->id);
                        //sort $children by orderIndex
                        // $children[] = Arr::sort($cate->toArray());
                        // $array = $cat->sortBy('orderIndex')->toArray();
                        // $children[] = $array;
                        // var_dump($array);

                        // $children = array_multisort($category->orderIndex, SORT_DESC, $children);

                        // $children = $children->sortBy('orderIndex');
                       
                        $category->children = $children;
                        //sort $children by orderIndex                        
                      
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
            $category->id = strval($category->id);
            $category->active = boolval($category->active);
            $category->sortsByOrderIndex = boolval($category->sortsByOrderIndex);
            $category->isManuallyControlled = boolval($category->isManuallyControlled);
            $category->hasSeparator = boolval($category->hasSeparator);
            // var_dump($category->toArray());
            $arr[] = array(
                'id' => strval($category->id),
                'name' => $category->name,
                'iconUrl' => $category->iconUrl,
                'active' => $category->active,
                'sortsByOrderIndex' => $category->sortsByOrderIndex,
                'isManuallyControlled' => $category->isManuallyControlled,
                'hasSeparator' => $category->hasSeparator,
                'children' => $category->children,
                'parentName' => $category->parentName,
                'orderIndex' => (float)$category->orderIndex,
                'type' => $category->type,

            
            );
            // $arr[] = $category->toArray();


        }

//         for ($i = 0; $i < $count; $i++)
//         {
//             $arr[] = array
//             (
//                 'id' => $cat[$i]["id"],
//                 'active' => $cat[$i]['active'],
//                 'isManuallyControlled' => $cat[$i]['isManuallyControlled'],
//                 'name' => $cat[$i]['name'],
//                 'sortsByOrderIndex' => $cat[$i]['sortsByOrderIndex'],
//                 'sortsAlphabetically' => $cat[$i]['sortsAlphabetically'],
//                 'orderIndex' => $cat[$i]['orderIndex'],
//                 'type' => $cat[$i]['type'],
//                 'iconUrl' => $cat[$i]['iconUrl'],
//                 'isControlledByPopularity' => $cat[$i]['isControlledByPopularity'],
//                 'hasSeparator' => $cat[$i]['hasSeparator'],
//                 'parentName' => $cat[$i]['parentName'],
//                 'parentId' => $cat[$i]['parentId'],
// //                'user' => array   // UserSMIINFO
// //                (
// //                    'id' => strval($user[$i]["id"]),
// //                    'firstName' => $user[$i]["firstName"],
// //                    'lastName' => $user[$i]["lastName"],
// //                    'emailAddress' => $user[$i]["email"],
// //                    'payingUser' => true,
// //                    //   'warningMessage' => "",
// //                    'buttonColor' => "",
// //                    'buttonReason' => ""
// //                ),
// //                // AVATAR SMI INFO
// //                'defaultAvatar' => array
// //                (
// //                    'id' => strval($user[$i]["id"]),
// //                    'nameInstance' => $avatar[$i]['nameInstance'],
// //                    'lastName' => $avatar[$i]['lastName'],
// //                    'firstName' => $avatar[$i]['firstName'],
// //                    'hasPet' => false,
// //                    'isOnline' => false,
// //                    'isDefault' => true,
// //                ),
//             );
//         }
        return $arr;
    }

    function updateSpaceCategoryOrder($data)
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
            $catId->orderIndex = $order;
            $catId->save();
        }
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

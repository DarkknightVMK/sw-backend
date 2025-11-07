<?php
 use App\Models\catalogCategories;
 use App\Models\catalogItemEntry;

class category
{

    function getActiveCategories($timeconfig, $cat, $unk)
    {
      return $this->getAllActiveCategories();
    }
    function getActiveSpaceCategoryTree()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.ListResult";
        $amf->list = array(
//            $this->catalogCategories(0, true, true, false, 1, "Official", false),
            $this->catalogCategories(1, false,false,false,2, "Popular", true, null, null)
        );
        $amf->success = true;
        return $amf;
    }
    function catalogCategories($orderIndex, $manual, $sortOI, $sortA, $id, $name, $pop, $child, $icon, $parent)
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.catalog.category.external.amf.result.CatalogCategoryResult";
        $amf->orderIndex = $orderIndex;
        $amf->isManuallyControlled = $manual;
        $amf->children = $child;
        $amf->hasSeparator = false;
        $amf->type = "item";
        $amf->sortsByOrderIndex = $sortOI;
        $amf->sortsAlphabetically = $sortA;
        $amf->id = $id;
        $amf->parentId = $parent;
        $amf->name = $name;
        $amf->isControlledByPopularity = $pop;
        $amf->active = true;
        $amf->iconUrl = $icon;
        $amf->success = true;
        $amf->parentName = null;
        return $amf;
    }

    function getAllActiveCategories()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.ListResult";
        $categories = catalogCategories::where('type', 'item')->where('active', 1)->get()->sortBy('orderIndex');
        $ret=array();
        foreach ($categories as $category) 
        {
            if ($category->children != null && $category->parentId == null)
            { //inside a category that has children
                $catIds = explode(",", strval($category->children));
            $catIds = Arr::sort($catIds, function($value) {
                        return catalogCategories::where('id', $value)->get()->first()->orderIndex;
                    });
                $children = array();
                foreach ($catIds as $catId) 
                {
                    $cat = catalogCategories::where('id', $catId)->where('active',1)->get();
                    // var_dump(count($cat));
                    
                    foreach ($cat as $cate)
                    {
                        //only returns 1 category :/
                        //  $ret[] = array();
                        // $category->children = ($cat->toArray());
                        
                            $children[] = $cate->toArray();
                            $category->children = $children;
                        
                        // else 
                        //     $category->children = null;
                        // $amf->list[] = $cate->toArray();
                    }
                                


                } 
                    
            // $category->children->toArray();
            }
            if (is_string($category->children))
            {
                $category->children = null;
            }

            if ($category->parentId != null || $category->active != 1)
            { //This is a child category
                continue;
            }
            // var_dump($catIds);
            // if ($category->parentId != null)
            //             break;

            // sort category by orderIndex
            // $sorted = $category->sortBy('orderIndex');
            // var_dump($sorted);
                $amf->list[] = $category->toArray();

        }
        $user = Auth::user();
        // if (Auth::check() && $user->primaryGroupId == 1)
        // {
            // $amf->list[] =  $this->catalogCategories(6, true, true, false, 999, "Everything", false, null, null, null);
        // }


        // );
        $amf->success = true;
        return $amf;
    }

    public function getChildren($id)
    {
        // $amf = new stdClass();
        // $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        // $amf->$explicitTypeField = "com.smallworlds.service.result.ListResult";
        $category = catalogCategories::where('id', $id)->where('active',1)->get();
        foreach($category as $cat)
        {
            $list[] = $cat->toArray();
        }
        return $list;
    }
}

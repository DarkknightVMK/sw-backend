<?php
use App\Models\catalogCategories;


class catalog_category
{
    function getActiveSpaceCategoryTree()
    {
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.service.result.ListResult";

        foreach (catalogCategories::where('type', 'space')->where('active', 1)->get()->sortBy('orderIndex') as $category) {
        if ($category->children != null && $category->parentId == null)
        {
            $catIds = explode(",", strval($category->children));
            $catIds = Arr::sort($catIds, function($value) {
                return catalogCategories::where('id', $value)->get()->first()->orderIndex;
            });

            $children = array();
            foreach ($catIds as $catId) 
            {
                $cat = catalogCategories::where('id', $catId)->get();
                
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
                
        }

        if ($category->parentId != null)
        { //This is a child category
            continue;
        }
    
            $category->active = boolval($category->active);
            $category->hasSeparator = boolval($category->hasSeparator);
            $category->isManuallyControlled = boolval($category->isManuallyControlled);
            $category->isControlledByPopularity = boolval($category->isControlledByPopularity);

            $ret->list[] = $category->toArray();
        }        
        
        
        $ret->success = true;
        return $ret;
    }
    function catalogCategories()
    {

        
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.catalog.category.external.amf.result.CatalogCategoryResult";
        $ret->orderIndex = $orderIndex;
        $ret->isManuallyControlled = $manual;
        $ret->children = null;
        $ret->hasSeparator = false;
        $ret->type = "S";
        $ret->sortsByOrderIndex = $sortOI;
        $ret->sortsAlphabetically = $sortA;
        $ret->id = $id;
        $ret->parentId = null;
        $ret->name = $name;
        $ret->isControlledByPopularity = $pop;
        $ret->active = true;
        $ret->success = true;
        $ret->parentName = null;
        return $ret;
    }
    function getAllActiveCategories()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

<?php
use App\Models\s2wCat;
use App\Models\s2wPrize;
use App\result\DataResult;
use App\result\ServiceResult;
use App\result\TypedRecordSetResult;

class category
{
    function addCategory($timeconfig, $name, $isDeluxe)
    {
        $cat = new s2wCat;
        $cat->name = $name;
        $cat->isDeluxe = $isDeluxe;
        $cat->save();
        return new DataResult(
            json_decode( $cat->where('id', $cat->id)->get()->first()->toJson() )
        );
                }

    function getCategories()
    {
        $amf = new stdClass();
        $cat = s2wCat::all()->toArray();

        // add 'prizes' to category array
        foreach ($cat as $key => $value) {
            $value['active'] = boolval($value['active']);
            $value['isDeluxe'] = boolval($value['isDeluxe']);
            $value['id'] = (string)$value['id'];
            // remove created_at and updated_at
            unset($value['created_at']);
            unset($value['updated_at']);
            // add startDate to category array
            $value['startDate'] = null;
            $value['endDate'] = null;
            $value['repeatTime'] = (float)0;
            // update cat
            $cat[$key] = $value;

            $prizes = s2wPrize::where('categoryId', $value['id'])->get()->toArray();
            $cat[$key]['prizes'] = $prizes;
            // $cat[$key]['prizes'] = array(array(
            //     'id' => "99",
            //     'isInPreviewList' => true,
            //     'isBraggable' => true,
            //     'type' => (float) 2,
            //     'categoryId' =>  $value['id'],
            //     'tokens' => "500",
            //     'gold' => "5000",
            //     'modelId' => "1344",
            //     'modelDesc' => "Snow Fox",
            //     'modelIcon' => "items/base/consumables/head/icon_con_head_item_25_snowfox.png",
            //     'modelType' => "item",
            //     'modelCount' => null,



            // ));
        }
        // $prizes = s2wPrize::where('categoryId', $cat['id'])->get()->toArray();
        // append prizes to category array
        // $cat['prizes'] = $prizes;
        
        $amf->recordSet = $cat;
        $amf->success=true;
        return new TypedRecordSetResult($cat);
    }

    function editCategory($timeconfig, $id, $name, $weighting, $background, $count, $schedStart, $schedEnd, $schedRepeat, $active)
    {
        $cat = s2wCat::where('id', $id)->get()->first();
        $cat->name = $name;
        $cat->weighting = $weighting;
        $cat->background = $background;
        $cat->count = $count;
        // $cat->schedStart = $schedStart;
        // $cat->schedEnd = $schedEnd;
        // $cat->schedRepeat = $schedRepeat;
        $cat->active = $active;
        $cat->save();
        return new ServiceResult;
    }

    function deleteCategory($timeconfig, $id)
    {
        $cat = s2wCat::where('id', $id)->get()->first();
        $cat->delete();
        return new ServiceResult;
    }
}

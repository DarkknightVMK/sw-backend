<?php
use App\Models\items;
use App\Models\spaceModels;


class model
{
    function getAllModels()
    {
        $amf = new stdClass();
        $amf->recordSet = $this->search('all');
        $amf->success=true;
        return $amf;
    }
    function getItemModels()
    {
        $amf = new stdClass();
        $amf->recordSet = $this->search('items');
        $amf->success=true;
        return $amf;
    }
    function getSpaceModels()
    {
        $amf = new stdClass();
        $amf->recordSet = $this->search('space');
        $amf->success=true;
        return $amf;
    }

    function search($modelType)
    {
        // $user = Users::where('id', $userID )->get();
        // $avatar = Avatars::where('avatar_id', $userID)->get();
        if ($modelType == 'items')
            $model = items::all();
        elseif ($modelType == 'all')
            $model = collect(items::all())->merge(collect(spaceModels::all()));
        else
            $model = spaceModels::all();
        $count = count($model);
        //loop($count, $spaces);
        // var_dump($count);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $arr[] = array 
            (
                'id' => (String)$model[$i]['model_id'],
                'icon' => $model[$i]['model_icon'],
                'desc' => ($modelType == 'items' ||$model[$i]['model_type'] == null ) ? $model[$i]['model_desc'] : $model[$i]['model_details'],
                'details' => ($modelType == 'items' || $model[$i]['model_type'] == null ) ? $model[$i]['model_details'] : $model[$i]['model_desc'],
                'source' => $model[$i]['model_source'],
                'type' => ($modelType == 'items' || $model[$i]['model_type'] == null ) ? "item" : $model[$i]['model_type'],
                'tags' => $model[$i]['model_tags'],
                'editControl' => "A",
                'accessControl' => "A",
                'moveControl' => "A",
                'priceTokens' => (float)$model[$i]['model_price_tokens'],
                'priceGold' => (float) $model[$i]['model_price_gold'],
                'premiumOnly' => boolval($model[$i]['model_premium_only']),
                'minXPLevel' => (float)$model[$i]['model_min_xplevel'],
            );
        }
        return $arr;
    }
    

}

<?php

use App\Models\avatarSpaces;
    use App\Models\spaceModels;
    use App\Models\Avatars;
    use App\Models\Users;
use Illuminate\Support\Facades\DB;
use App\Http\Services\functions;

class model extends functions
{
    function getMarketPlaceModels()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }

    function getPricedSpaceModels()
    {
        $avatar = new functions();

        $spaces = spaceModels::where('model_price', '>', 0)->get();

        $amf = new stdClass();
        $count = count($spaces);
        //loop($count, $spaces);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $arr[] = array (

                'id' => $spaces[$i]["model_id"],
                'desc' => $spaces[$i]["model_details"],
                'details' => $spaces[$i]["model_desc"],
                'accessControl' => $spaces[$i]["accessControl"],
                'editControl' => "C",
                'gameId' => $spaces[$i]['model_game_id'],
                'premiumOnly' => $spaces[$i]['model_premium_only'],
                'tags' => $spaces[$i]['model_tags'],
                'type' => $spaces[$i]['model_type'],
                'priceTokens' => $spaces[$i]['model_price_tokens'],
                'cid' => $spaces[$i]['model_cid'],
                'category' => $spaces[$i]['model_category'],
                'source' => $spaces[$i]['model_source'],
                'price' => $spaces[$i]['model_price'],
                // 'modelId' => strval($spaces[$i]["model_id"]),
                // 'salePrice' => 0,
                // 'salePriceTokens' => 10,
                // 'lastPurchasedAmount' => 0,
                // 'lastPurchasedTokens' => 0,
                // 'forSale' => "Y",
                // 'spaceOwner' => $spaces[$i]["avatar_id"],
                // 'avatarId' => $avatar->getAvatar('avatar_id'),
                // 'avatarFName' =>$avatar->getAvatar('firstName'),
                // 'avatarLName' =>$avatar->getAvatar('lastName'),
                // 'avatarDetails' => null,
                'icon' => null,

                // 'spaceRoleAccess' => "0",
                // 'modelPrice' => $spaces[$i]["price"],


                // 'modelSale' => true



            );
        }

        $amf->recordSet = $arr;
        $amf->success = true;

return $amf;

    }
}

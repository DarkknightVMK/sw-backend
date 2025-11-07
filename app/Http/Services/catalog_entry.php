<?php

include ('functions.php');
use App\Models\avatarSpaces;
use App\Models\spaceModels;
use App\Models\Avatars;
use App\Models\catalogCategories;
use App\Models\catalogSpaceEntry;
use App\Models\spaceFavorites;
use App\Models\onlineUsers;
use App\Http\Services\functions;
use App\result\TypedRecordSetResult;
use App\result\space\SpacePanelItem;

class catalog_entry extends functions
{
    function getSpacesForCategory($timeconfig, $categoryId, $skip, $take)
    {
        //1 = official  2 = popular
        // if ($categoryId == '1')
        // {
        //     $ret = new stdClass();
        //     $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        //     $ret->$explicitTypeField = "com.smallworlds.service.result.TypedRecordSetResult";
        //     $ret->recordSet = $this->getFeaturedSpaces();
        //     $ret->startIndex = $skip;
        //     $ret->maxLength = $take;
        //     $ret->totalCount = null;
        //     $ret->success = true;
        //     return $ret;
        // }
        // if ($categoryId == '2')
        // {
        //     $ret = new stdClass();
        //     $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        //     $ret->$explicitTypeField = "com.smallworlds.service.result.TypedRecordSetResult";
        //     $ret->recordSet = $this->getPopularSpaces();
        //     $ret->startIndex = $skip;
        //     $ret->maxLength = $take;
        //     $ret->totalCount = null;
        //     $ret->success = true;
        //     return $ret;
        // }

        $ret = new stdClass();
        $avatar = new functions();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.service.result.TypedRecordSetResult";
        // $ret->recordSet = \
        $popular = catalogCategories::where('isControlledByPopularity', true)->first();
        if ($popular && $categoryId == 56)
        {
            // $pop = onlineUsers::all();
            $ret->recordSet = $this->getPopularSpaces();
            return $ret;
        }

        return new TypedRecordSetResult(  
            json_decode(catalogSpaceEntry::where('categoryId', $categoryId)->orderBy('orderIndex')->get()->map(function ($space) {
                  return new SpacePanelItem(avatarSpaces::where('id', $space->spaceId)->get()->first());
            })));
        //foreach (catalogSpaceEntry::where('categoryId', $categoryId)->skip($skip)->take($take)->get()->sortBy('orderIndex') as $space) 
        // foreach (catalogSpaceEntry::where('categoryId', $categoryId)->get()->sortBy('orderIndex') as $space)
        // {
        //     $spaceInfo = avatarSpaces::where('id', $space->spaceId)->first();
        //     $fav = spaceFavorites::where('space_id', $space->spaceId)->where('avatar_id', $avatar->getAvatar('avatar_id'))->first();
        //     // $spaceInfo->isFavorite = $fav ? true : false;
        //     $currentVisitors = onlineUsers::where('space_id', $spaceInfo->id)->where('online', true)->count();


        //     $space->id = $space->spaceId;
        //     $space->spaceOwner = $spaceInfo->avatar_id;
        //     $space->avatarId = $avatar->getAvatar('avatar_id');
        //     $space->avatarFName =$avatar->getAvatarByID($spaceInfo->avatar_id,'firstName');
        //     $space->avatarLName =$avatar->getAvatarByID($spaceInfo->avatar_id,'lastName');
        //     $space->avatarDetails = null;
        //     $space->desc = $spaceInfo->name;
        //     $space->details = $spaceInfo->desc;
        //     $space->type = $spaceInfo->type;
        //     $space->forSale = $spaceInfo->forSale;
        //     $space->salePrice = $spaceInfo->salePriceGold;
        //     $space->salePriceTokens = $spaceInfo->salePriceTokens;
        //     $space->accessControl = $spaceInfo->accessControl;
        //     $space->modelId = $spaceInfo->modelId;
        //     $space->spaceRoleAccess = "0";
        //     $space->currentVisitors = $currentVisitors;
        //     $space->modelPrice = $spaceInfo->modelPriceGold;
        //     $space->iconSource = $spaceInfo->icon;
        //     $space->fav = $fav ? true : false;
        //     // $space->modelPriceTokens = $spaceInfo->modelPriceTokens;
        //     $space->thumbnailSource = $spaceInfo->spaceThumbnailSource;
        //     // $space->avatar = $this->getAvatar($space->avatar_id);
        //     // $space->model = $this->getModel($space->model_id);
        //     // $space->category = $this->getCategory($space->catalog_category_id);
        //     $ret->recordSet[] = $space->toArray();
        // }
        // $ret->success = true;
        // $ret->startIndex = $skip;
        // $ret->maxLength = $take;
        // $ret->totalCount = null;
        // return $ret;
    }
    function getPopularSpaces()
    {
        $avatar = new functions();
        $online = onlineUsers::where('online', true)->where('local',false)->get()->toArray();
        // find space id duplicates
        $duplicates = array_count_values(array_column($online, 'space_id'));
        // get array keys value
        // var_dump($duplicates);
        // $keys = array_keys($duplicates, max($duplicates));
        // get array values
        $values = array_values($duplicates);
        $spaceId = array_keys($duplicates);
        // var_dump($spaceId);
// echo "spaceId: " . $spaceId[1];
        // remove duplicates
        $unique = array_keys($duplicates, 1);
        // get online users for each space
        // $onlineUsers = onlineUsers::whereIn('space_id', $unique)->get();
        // var_dump($onlineUsers);
    //    var_dump($duplicates);
        //    {
        //        $space = avatarSpaces::where('id', $onlineUser->space_id)->first();
       
// var_dump($space);
        $ret = new stdClass();
        $count = count($spaceId);
        // var_dump($count);
        //loop($count, $spaces);
        $arr = array();
        foreach ($spaceId as $key => $value) {
            $space = avatarSpaces::where('id', $value)->first();
            $spaceModel = spaceModels::where('model_id', $space->modelId)->first();

            // var_dump($value);
            if ($space->showInPlacePanel == true) {
            
                $arr[] = array
                (
                    'id' => $space->id,
                    'desc' => $space->name,
                    'details' => $space->desc,
                    'type' => $space->type,
                    'forSale' => $space->forSale,
                    'accessControl' => $space->accessControl,
                    'modelId' => $space->modelId,
                    'spaceRoleAccess' => "0",
                    'currentVisitors' => $values[$key],
                    'avatarId' => $avatar->getAvatar('avatar_id'),
                    'avatarFName' =>$avatar->getAvatarByID($space->avatar_id,'firstName'),
                    'avatarLName' =>$avatar->getAvatarByID($space->avatar_id,'lastName'),
                    'avatarDetails' => null,
                    'iconSource' => $space->icon,
                    'modelPriceTokens' => $space->salePriceTokens,
                    'modelPrice' => (float) $space->salePriceGold,
                    'thumbnailSource' => $space->spaceThumbnailSource,
                );
            }
        }
        return $arr;
    }
    function getFeaturedSpaces()
    {
        $avatar = new functions();

        $spaces = avatarSpaces::whereIn('id', [84,10,78,176,77,141,25,8,173,244])->get();
        $ret = new stdClass();
        $count = count($spaces);
        //loop($count, $spaces);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $arr[] = array (
                'id' => $spaces[$i]["id"],
                'spaceOwner' => $spaces[$i]["avatar_id"],
                'avatarId' => $avatar->getAvatar('avatar_id'),
                'avatarFName' =>$avatar->getAvatarByID($spaces[$i]["avatar_id"],'firstName'),
                'avatarLName' =>$avatar->getAvatarByID($spaces[$i]["avatar_id"],'lastName'),
                'avatarDetails' => null,
                'desc' => $spaces[$i]["name"],
                'details' => $spaces[$i]["desc"],
                'type' => "A",
                'iconSource' => $spaces[$i]["icon"],
                'forSale' => "N",
                'accessControl' => $spaces[$i]["accessControl"],
                'modelId' => $spaces[$i]["modelId"],
                'spaceRoleAccess' => "0",
                'currentVisitors'=> $spaces[$i]["currentVisitors"],
                'thumbnailSource' => $spaces[$i]['spaceThumbnailSource'],

            );

        }
        return $arr;
    }
}

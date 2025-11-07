<?php
    include ('results/RecordSetResult.php');
include_once ('functions.php');
require_once(__DIR__ . '../../../../resources/php/config.php');

use App\Models\avatarSpaces;
use App\Models\avatarItems;
use App\Models\items;

    use App\Models\spaceModels;
    use App\Models\Avatars;
    use App\Models\Avatar;
    use App\Models\Users;
    use App\Models\spaceFavorites;
    use App\Models\spaceMember;
    use App\Models\spaceRoles;
    use App\Models\spaceBans;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\onlineUsers;
use App\Http\Services\functions;
use App\result\RecordSetResult;
use App\result\BooleanResult;
use App\result\ServiceResult;
use App\result\TypedRecordSetResult;
use App\result\space\SpacePanelItem;
use App\result\space\RoleResult;
use App\result\space\SpaceBanInfo;
use App\result\ErrorCodes;
use App\result\UserPermissions;

class space extends functions
{

    function viewMembers($timeconfig, $spaceId)
    {
        $arr[] = array(
            'spacerole_id',
            'spacerole_access_flag',
            'avatar_name_instance',
            'avatar_fname',
            'spacerole_desc',
            'spacerole_officer_flag',
            'avatar_name',
            'spacemember_space_id',
            'spacerole_edit_flag',
            'spacemember_spacerole_id',
            'avatar_lname',
            'spacemember_id',
            'spacemember_avatar_id',
            'avatar_online',
            'avatar_online_at'
        );
        if (spaceMember::where('space_id', $spaceId)->exists())
        {

            foreach(spaceMember::where('space_id',$spaceId)->get() as $space)
            {
                $spaceRole = spaceRoles::where('id', $space->spacerole_id)->first();
               $arr[] = array
               (
                (float)$spaceRole->id,
                    $spaceRole->access_flag,
                    (float)functions::getAvatarByID( $space->avatar_id, 'nameInstance'),
                    functions::getAvatarByID( $space->avatar_id, 'firstName'),
                    $spaceRole->desc,
                    $spaceRole->officer_flag,
                    functions::getAvatarByID( $space->avatar_id, 'firstName') . ' ' . functions::getAvatarByID( $space->avatar_id, 'lastName'),
                    (float)$space->space_id,
                    $spaceRole->edit_flag,
                    (float)$space->spacerole_id,
                    functions::getAvatarByID( $space->avatar_id, 'lastName'),
                    (float)$space->id,
                    $space->avatar_id,
                    (onlineUsers::where('avatar_id', $space->avatar_id)->where('online',true)->where('local', false)->exists()) ? 'Y' : 
                    'N',
                    (onlineUsers::where('avatar_id', $space->avatar_id)->exists()) ? new \Amfphp_Core_Amf_Types_Date( Carbon::createFromTimestamp(strtotime(onlineUsers::where('avatar_id', $space->avatar_id)->first()->updated_at))->valueOf()) : null
                );
                // $this->addRecord($arr);
            }
            return new RecordSetResult($arr);
        }
        return new RecordSetResult(array(

        ));
    }

    function canAccessSpace($timeconfig, $spaceID)
    {
        //check permissions
        if (in_array(UserPermissions::SPACE_ACCESS_ANY, functions::getUserPermissions()))
        {
            return new BooleanResult(true);
        }
        //is owner of space

        if (spaceMember::where('space_id', $spaceID)->exists() || avatarSpaces::where('id', $spaceID)->where('user_id', session('user'))->exists())
        {
            if (avatarSpaces::where('id', $spaceID)->where('user_id', session('user'))->exists())
                return new ServiceResult(true);
            
            $spaceMember = spaceMember::where('space_id', $spaceID)->where('avatar_id', session('avatar'))->first();
            if ($spaceMember != null )
            {
                return new BooleanResult(true);
            }
        }
        return new ServiceResult(false);
    }

    function getRole($timeconfig,  $spaceID)
    {
        // return $amf;
        $space = avatarSpaces::where('id', $spaceID)->first();
        return new RoleResult($space);
    }

    function viewSpaceRoles($timeconfig, $spaceID)
    {
        return new RecordSetResult(array());
    }
    function isSpaceOwner($timeconfig, $spaceId)
    {
        if (avatarSpaces::where('id', $spaceId)->where('user_id',session('user'))->exists())
            return new BooleanResult(true);
        return new BooleanResult(false);
    }

    function viewSpacesIModerate($timeconfig, $avatarID)
    {
        //todo error checking
        return new TypedRecordSetResult(  
            json_decode(avatarSpaces::where('user_id', session('user'))->get()->map(function ($space) {
                  return new SpacePanelItem($space);
            })));
    }
    function viewBans($timeconfig, $spaceID)
    {
        return new TypedRecordSetResult(
            json_decode(spaceBans::where('space_id', $spaceID)->get()->map(function ($ban) {
                  return new SpaceBanInfo($ban);
            })));
        
    }
    function addSpaceToFav($timeconfig, $spaceID)
    {
        $amf = new stdClass();
        $fav = spaceFavorites::where('space_id', $spaceID)->where('avatar_id', session('avatar'))->first();
        if ($fav == null) {
            $fav = new spaceFavorites();
            $fav->space_id = $spaceID;
            $fav->avatar_id = session('avatar');
            $fav->save();        
            $amf->success=true;
        }
        else {
            $amf->success=false;
        }
        return $amf;
    }

    function removeSpaceFromFav($timeconfig, $spaceID)
    {
        $amf = new stdClass();
        $fav = spaceFavorites::where('space_id', $spaceID)->where('avatar_id', session('avatar'))->first();
        if ($fav != null) {
            $fav->delete();
            $amf->success=true;
        }
        else {
            $amf->success=false;
        }
        return $amf;
    }

    function viewMyFavSpaces($timeconfig, $seek, $peek)
    {
        $amf = new stdClass();
        $avatar = new functions();
        $spaces = spaceFavorites::where('avatar_id', session('avatar'))->get();
        foreach ($spaces as $space) {
            $spaceDetails = spaceFavorites::find($space->space_id)->space;
            $currentVisitors = onlineUsers::where('space_id', $space->space_id)->where('online', true)->count();
            $spaceModel = spaceModels::where('model_id', $spaceDetails->modelId)->first();

            //SPACEPANELRESULT
            $space->id = (string) $space->space_id;
            $space->spaceOwner = $spaceDetails->avatar_id;
            $space->avatarId = $avatar->getAvatar('avatar_id');
            $space->avatarFName =$avatar->getAvatarByID($spaceDetails->avatar_id,'firstName');
            $space->avatarLName =$avatar->getAvatarByID($spaceDetails->avatar_id,'lastName');
            $space->avatarDetails = null;
            $space->desc = $spaceDetails->name;
            $space->details = $spaceDetails->desc;
            $space->type = $spaceDetails->type;
            $space->forSale = $spaceDetails->forSale;
            $space->accessControl = $spaceDetails->accessControl;
            $space->modelId = $spaceDetails->modelId;
            $space->spaceRoleAccess = "0";
            $space->currentVisitors = $currentVisitors;
            $space->modelPrice = $spaceDetails->modelPriceGold;
            $space->iconSource = $spaceDetails->icon;
            $space->fav = true;
            $space->modelPrice = $spaceModel->model_price;
            // $space->modelPriceTokens = $spaceInfo->modelPriceTokens;
            $space->thumbnailSource = $spaceDetails->spaceThumbnailSource;
            // $space->avatar = $this->getAvatar($space->avatar_id);
            // $space->model = $this->getModel($space->model_id);
            // $space->category = $this->getCategory($space->catalog_category_id);
            $amf->recordSet[] = $space->toArray();
        
        
        }
        $amf->success = true;
        $amf->startIndex = $seek;
        $amf->maxLength = $peek;
        $amf->totalCount = count($spaces);
        // $favSpaces = spaceFavorites::find(session('avatar'))->avatar;
        // var_dump($favSpaces);
        $amf->success=true;
        return $amf;
    }

    function spaceAuth($timeconfig, $spaceID, $password)
    {
        $pass = (avatarSpaces::where('id', $spaceID)->first()->password != null) ? avatarSpaces::where('id', $spaceID)->first()->password : "";
        if ($pass != "" && Hash::check($password, avatarSpaces::where('id', $spaceID)->first()->password)) 
            return new ServiceResult(true);
        elseif ($password != "" )
            return new ServiceResult(false, ErrorCodes::LOGIN_INVALID_PASSWORD);
        else
        return new ServiceResult(false, ErrorCodes::INTERNAL_ERROR);
    }

  
    function viewSpaceItems($timeconfig, $spaceID) // offset and row
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.TypedRecordSetResult";
        $amf->recordSet = $this->getspaceItems($spaceID);
        // $amf->startIndex = $skip;
        // $amf->maxLength = $take;
        $amf->totalCount = null;
        $amf->success = true;
        return $amf;

    }
    function getspaceItems($spaceID)
    {
        $avatar = new functions();

        $items = avatarItems::where('space_id','=', $spaceID)->get();
        $count = count($items);
        $amf = new stdClass();
// $models = items::where('model_id', $items[0]["model_id"])->get();
// echo $models[0]["model_id"];
        $arr[] = array(
            "item_widget_persistence",
            "item_space_id",
            "item_sale_price",
            "item_gift_open_date",
            "item_gift_secret_msg",
            "item_access_control",
            "item_active",
            "item_last_purchased_with_model_id",
            "item_owner_id",
            "item_last_purchased_amount",
            "item_move_control",
            "item_count",
            "item_last_purchased_tokens",
            "item_gift_wrap_model_id",
            "item_config",
            "item_model_id",
            "item_tournament_id",
            "item_sale_price_tokens",
            "item_edit_control",
            "item_icon_postfix",
            "item_gifted_by_avatar",
            "item_id",
            "item_for_sale",
            "item_gifted_by_user",
            "item_purchased_with_tokens"
        );

        for ($i = 0; $i < $count; $i++)
        {
            $models = items::where('model_id', $items[$i]["model_id"])->get();
            $arr[] = array (
                "N",
                strval($spaceID),
                null,
                null,
                null,
                "A",
                "Y",
                null,
                strval($avatar->getAvatar('avatar_id')),
                null,
                "A",
                $items[$i]["item_count"],
                $models[0]["model_price_tokens"],
                null,
                $items[$i]["item_config"],
                $models[0]["model_id"],
                null,
                null,
                "A",
                null,
                null,
                $items[$i]["item_id"],
                "N",
                null,
                "Y"

            // 'modelPriceTokens' => $models[0]["model_price_tokens"],
            // 'modelClassId' => $models[0]["model_cid"],
            // 'modelTags' =>$models[0]["model_tags"],
            // 'giftedByAvatarLastName' => null,
            // 'modelAllowedCount' => 0,
            // 'iconPostfix' => $items[$i]["item_icon_postfix"],
            // 'lastPurchasedWithTokens' => $items[$i]["item_last_purchased_tokens"],
            // 'modelSource' => $models[0]["model_source"],
            // 'modelDetails' => $models[0]["model_details"],
            // 'modelDesc' => $models[0]["model_desc"],
            // 'config' => ,
            // 'giftedByAvatarId' => null,
            // 'hasPersistedConfig' => false,
            // 'giftWrapModelSource' => $items[$i]["giftWrapModel_source"],
            // 'modelMinXPLevel' => $models[0]["model_min_xplevel"],
            // 'modelPriceGold' => $models[0]["model_price_gold"],
            // 'itemId' => ,
            // 'modelId' => ,
            // 'modelIcon' => $models[0]["model_icon"],
            // 'valueTokens' =>  , // LAST PURCHASED AMOUNT
            // 'valueGold' =>  $models[0]["model_price_gold"],
            // 'success' => true
        );
       }
       return $arr;

    }


    function getSpaceInfoFromAlias($timeconfig, $alias)
    {
        $avatarSpace = avatarSpaces::where('alias', $alias)->get()[0];
        $modelId = $avatarSpace["modelId"];
        $model = spaceModels::where('model_id', $modelId)->get()[0];
        $avatarId = $avatarSpace["avatar_id"];
        $avatar = Avatars::where('avatar_id', $avatarId)->get()[0];
        $amf = new stdClass();
        $fav = spaceFavorites::where('space_id', $avatarSpace["id"])->where('avatar_id', session('avatar'))->first();
        $currentVisitors = onlineUsers::where('space_id', $avatarSpace["id"])->where('online', true)->where('local',false)->count();

        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.entity.space.result.SpaceInfoResult";
        $amf->lastPurchasedTokens = null;
        $amf->desc = $avatarSpace["name"];
        $amf->avatarDetails = null;
        $amf->avatarId = $avatarSpace["avatar_id"];
        $amf->ownerId = strval($avatarSpace["user_id"]);
        $amf->avatarNameInstance = (float) 1;
        $amf->votes = null;
        $amf->accessControl = $avatarSpace["accessControl"];
        $amf->avatarFName = $avatar["firstName"];
        $amf->type = $avatarSpace["type"];
        $amf->avatarLName = $avatar["lastName"];
        $amf->id = (string) $avatarSpace["id"];
        $amf->modelSource = $model["model_source"];
        $amf->details = $avatarSpace["desc"];;
        $amf->spaceGroupId = $avatarSpace["spaceGroupId"];
        $amf->hasRated = null;
        $amf->config = $avatarSpace["config"];
        $amf->lastPurchasedAmount = 0;
        $amf->isFav = $fav ? true : false;
        $amf->forSale = $avatarSpace["forSale"];
        $amf->currentVisitors = $currentVisitors;
        $amf->spaceSnapshotSource = $avatarSpace["spaceSnapShotSource"];
        $amf->isFirstTimeUser = true;
        $amf->spaceJoinChannel = $avatarSpace["spaceJoinChannel"];
        $amf->isUserVip = true;
        $amf->spaceIconSource = $avatarSpace["icon"];
        $amf->spaceShowAds = $avatarSpace["spaceShowAds"];
        $amf->modelId = strval($modelId);
        $amf->spaceThumbnailSource = $avatarSpace["spaceThumbnailSource"];
        $amf->rating = (float) $avatarSpace["rating"];
        $amf->spaceShowInSearch = boolval($avatarSpace["showInPlacePanel"]);
        $amf->spaceGroupName = null;
        $amf->success = true;
        return $amf;


    }
    function getSpaceInfo($timeconfig, $spaceId)
    {
        $avatarSpace = avatarSpaces::where('id', $spaceId)->first();
        $modelId = $avatarSpace["modelId"];
        $model = spaceModels::where('model_id', $modelId)->get()[0];
        $avatarId = $avatarSpace["avatar_id"];
        $avatar = Avatars::where('avatar_id', $avatarId)->get()[0];
        $fav = spaceFavorites::where('space_id', $avatarSpace["id"])->where('avatar_id', session('avatar'))->first();
        $currentVisitors = onlineUsers::where('space_id', $avatarSpace["id"])->where('online',true)->where('local',false)->count();

        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.entity.space.result.SpaceInfoResult";
        $amf->success = true;

        $amf->lastPurchasedTokens = (empty($avatarSpace['lastPurchasedTokens'])) ? (float) $model['model_price_tokens']: $avatarSpace['lastPurchasedTokens'];
        $amf->desc = $avatarSpace["name"];
        $amf->avatarDetails = null;
        $amf->avatarId = $avatarSpace["avatar_id"];
        $amf->ownerId = (string)$avatarSpace["user_id"];
        $amf->avatarNameInstance = (float) 1;
        $amf->votes = null;
        $amf->accessControl = $avatarSpace["accessControl"];
        $amf->avatarFName = $avatar["firstName"];
        $amf->type = $avatarSpace["type"];
        $amf->avatarLName = $avatar["lastName"];
        $amf->id = (string) $spaceId;
        $amf->modelSource = $model["model_source"];
        $amf->details = $avatarSpace["desc"];
        $amf->spaceGroupId = $avatarSpace["spaceGroupId"];
        $amf->forSale = $avatarSpace["forSale"];
        $amf->price = (float)$avatarSpace["salePriceGold"];
        $amf->hasRated = null;
        $amf->config = $avatarSpace["config"];
        $amf->lastPurchasedAmount = ($avatarSpace['lastPurchasedAmount'] == null) ? (float) $model['model_price']: $avatarSpace['lastPurchasedAmount'];
        $amf->isFav = $fav ? true : false;
        $amf->currentVisitors = (float) $currentVisitors;
        $amf->spaceSnapshotSource = $avatarSpace["spaceSnapShotSource"];
        $amf->isFirstTimeUser = true;
        $amf->spaceJoinChannel = $avatarSpace["spaceJoinChannel"];
        $amf->isUserVip = true;
        $amf->spaceIconSource = $avatarSpace["icon"];
        $amf->spaceShowAds = boolval($avatarSpace["spaceShowAds"]);
        $amf->modelId = (string)$modelId;
        $amf->spaceThumbnailSource = $avatarSpace["spaceThumbnailSource"];
        $amf->rating = (float)$avatarSpace["rating"];
        $amf->spaceShowInSearch = boolval($avatarSpace["showInPlacePanel"]);
        $amf->spaceGroupName = null;
        return $amf;


    }

    function  isSpacePriceCapped($timeconfig, $spaceId)
    {
        $amf = new stdClass();
        $amf->value = false;
        $amf->success = true;
        return $amf;
    }

    // function purchaseSpace($timeconfig, $unk, $unk1, $id)
    // {
    //     return new ServiceResult;
    // }

    

    function saveSpaceSnapshot($timeconfig, $space_id, $source, $thumb, $challengeID, $md5)
    {
        $amf = new stdClass();

        if (avatarSpaces::where('avatar_id', session('avatar'))->where('id', $space_id)->get()->toJson() != '[]' || in_array(UserPermissions::SPACE_ADMIN, functions::getUserPermissions(session('avatar'))))
        {
            if (in_array(UserPermissions::SPACE_ADMIN, functions::getUserPermissions(session('avatar'))))
                avatarSpaces::where('id', $space_id)->update(['spaceSnapShotSource' => $source, 'spaceThumbnailSource' => $thumb]);
            else
                avatarSpaces::where('avatar_id', session('avatar'))->where('id', $space_id)->update(['spaceSnapShotSource' => $source, 'spaceThumbnailSource' => $thumb]);
            // spaceSnapShotSource
            // $amf->spaceSnapshotSource = $source;
            // $amf->spaceSnapshotThumbnail = $thumb;      
            $amf->success = true;

        }
        else
        {
            $amf->code = 5;      
            $amf->success = false;
        }
        // $amf->success = true;
        return $amf;
    }
    function getSpaceAddress($timeconfig, $id, $param)
    {
        $alias = avatarSpaces::where('id', $id)->pluck('alias')->first();
        if ($alias != null)
        {
            $id = avatarSpaces::where('id', $id)->get()[0]['alias'];
        }
        $amf = new stdClass();
        $amf->success = true;
        $amf->data = "space/" . $id ."/";
        return $amf;
    }
    function getSpaceAlias($timeconfig, $spaceID)
    {
        //implement Alias in DB
        $amf = new stdClass();
        $amf->success = true;
        $amf->data = null;
        return $amf;
    }
    function makeNewHomeSpace($timeconfig, $id)
    {
        $avatar = new functions();

        if (avatarSpaces::where('avatar_id',$avatar->getAvatar('avatar_id'))->where('id', $id)->get() != null)
        {
            Avatars::where('avatar_id', $avatar->getAvatar('avatar_id'))->update(['homeSpaceId' => $id]);
            $amf = new stdClass();
            $amf->success = true;
            return $amf;
        }else {

            $amf = new stdClass();
            $amf->success = false;
            return $amf;
        }
    }
    function getSpaceRating($param, $param2)
    {
        $amf = new stdClass();
        $amf->success = true;
        return $amf;
    }
    function viewSpacesIEdit()
    {

        $timeconfig = null; $skip = null; $take = null;
        return $this->getMySpaces($timeconfig, $skip, $take);
    }
    function viewMySpaces($timeconfig, $skip, $take) // offset and row
    {
        // $amf = new stdClass();
        // $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        // $amf->$explicitTypeField = "com.smallworlds.service.result.TypedRecordSetResult";
        // $amf->recordSet = $this->getMySpaces($skip, $take);
        // $amf->startIndex = $skip;
        // $amf->maxLength = $take;
        // $amf->totalCount = null;
        // $amf->success = true;
        // return $amf;
        return new TypedRecordSetResult(  
            json_decode(avatarSpaces::where('user_id', session('user'))->where('active', true)->get()->map(function ($space) {
                  return new SpacePanelItem($space);
            })));

    }
    function viewSpaces($spaceID) // offset and row
    {
        $amf = new stdClass();
        if ($spaceID != null)
        {
            $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
            $amf->$explicitTypeField = "com.smallworlds.service.result.TypedRecordSetResult";
            $amf->recordSet = $this->getSpaceByID($spaceID);
            // $amf->startIndex = $skip;
            // $amf->maxLength = $take;
            $amf->totalCount = null;
        }
        $amf->success = true;
        return $amf;

    }
    function getSpaceByID($spaceID)
    {
        $avatar = new functions();

        $spaces = avatarSpaces::where('id', $spaceID)->get();
        $currentVisitors = onlineUsers::where('space_id', $spaceID)->count();

        $amf = new stdClass();
        $count = count($spaces);
        //loop($count, $spaces);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $arr[] = array (

            'id' => $spaces[$i]["id"],
            'spaceOwner' => $spaces[$i]["avatar_id"],
            'avatarId' => $avatar->getAvatar('avatar_id'),
            'avatarFName' =>$avatar->getAvatar('firstName'),
            'avatarLName' =>$avatar->getAvatar('lastName'),
            'avatarDetails' => null,
            'desc' => $spaces[$i]["name"],
            'details' => $spaces[$i]["desc"],
            'type' => "A",
            'forSale' => "N",
            'iconSource' => $spaces[$i]["icon"],
            'accessControl' => $spaces[$i]["accessControl"],
            'modelId' => $spaces[$i]["modelId"],
            'spaceRoleAccess' => "0",
            'currentVisitors' => $currentVisitors,
            'thumbnailSource' => $spaces[$i]['spaceThumbnailSource'],

        );

        }
        return $arr;
    }

    function getAllSpaces()
    {
        $avatar = new functions();

        $spaces = avatarSpaces::all();

        $amf = new stdClass();
        $count = count($spaces);
        //loop($count, $spaces);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $currentVisitors = onlineUsers::where('space_id', $spaces[$i]["id"])->count();

            $arr[] = array (

            'id' => $spaces[$i]["id"],
            'spaceOwner' => $spaces[$i]["avatar_id"],
            'avatarId' => $avatar->getAvatar('avatar_id'),
            'avatarFName' =>$avatar->getAvatar('firstName'),
            'avatarLName' =>$avatar->getAvatar('lastName'),
            'avatarDetails' => null,
            'desc' => $spaces[$i]["name"],
            'details' => $spaces[$i]["desc"],
            'type' => "A",
            'forSale' => "N",
            'iconSource' => $spaces[$i]["icon"],
            'accessControl' => $spaces[$i]["accessControl"],
            'modelId' => $spaces[$i]["modelId"],
            'spaceRoleAccess' => "0",
            'currentVisitors' => $currentVisitors,
            'thumbnailSource' => $spaces[$i]['spaceThumbnailSource'],

        );

        }
        return $arr;
    }

    function getMySpaces($skip, $take)
    {
        $avatar = new functions();

        $spaces = avatarSpaces::where('user_id',session('user'))->get();

        $amf = new stdClass();
        $count = count($spaces);
        //loop($count, $spaces);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $currentVisitors = onlineUsers::where('space_id', $spaces[$i]["id"])->count();

            $arr[] = array (

            'id' => $spaces[$i]["id"],
            'spaceOwner' => $spaces[$i]["avatar_id"],
            'avatarId' => $avatar->getAvatarByID($spaces[$i]["avatar_id"], 'avatar_id'),
            'avatarFName' =>$avatar->getAvatarByID($spaces[$i]["avatar_id"], 'firstName'),
            'avatarLName' =>$avatar->getAvatarByID($spaces[$i]["avatar_id"],'lastName'),
            'avatarDetails' => null,
            'desc' => $spaces[$i]["name"],
            'details' => $spaces[$i]["desc"],
            'type' => $spaces[$i]["type"],
            'forSale' => $spaces[$i]["forSale"],
            // 'modelPrice' => $spaces[$i]["modelPriceGold"],
            // 'modelPriceTokens' => $spaces[$i]["modelPriceTokens"],
            'salePrice' => $spaces[$i]["salePriceGold"],
            'salePriceTokens' => $spaces[$i]["salePriceTokens"],
            'iconSource' => $spaces[$i]["icon"],
            'accessControl' => $spaces[$i]["accessControl"],
            'modelId' => $spaces[$i]["modelId"],
            'spaceRoleAccess' => "0",
            'currentVisitors' => $currentVisitors,
            'thumbnailSource' => $spaces[$i]['spaceThumbnailSource'],
            
        );

        }
        return $arr;
    }
    function loop($count, $spaces)
    {
        $avatar = new functions();

        $amf = new stdClass();



        return $amf;
    }

    function viewAvatarSpaceModels(): RecordSetResult
    { //TODO
        $amf = new RecordSetResult();
        // $spaceModels = $this->getAllSpaceModels();
        // $key = array_keys($spaceModels);
        $amf->recordSet = $this->allSpaceModels();

        //     $amf->recordSet =
        //     //array($this->defaultSpaceModels()) + $this->getAllSpaceModels();
        //     array
        // (
        //     $this->defaultSpaceModels(),
        //     $this->defaultSpaceModels(1,"com.smallworlds.entity.space.Space", null,null,null, "A player's first house", "C", "B", "spaces/models/house_suburban_lvl0.xml", "A", "FTU House", null, "2638", "N", 0,"space", null, "C", null, 0, null, 1),
        //     $this->defaultSpaceModels(1,"com.smallworlds.entity.space.Space", null,null,null, "A modern home set in the Hollywood Hills, complete with swimming pool", "C", "B", "spaces/models/house_hollywood_01_twostory_pool.xml", "A", "The Hollywood", null, "2370", "N", 0,"space", null, "C", null, 0, 8800, 1),
        //     $this->defaultSpaceModels(1,"com.smallworlds.entity.space.Space", 'spaces/models/regular_room02.png',null,null, "A little renovation could make this hidden gem shine", "C", "B", "spaces/models/regular_room02.xml", "A", "Little Room", null, "464", "N", 0,"space", null, "C", null, 0, null, 1),




        // );
        // $amf->key = $key;
        $amf->success = true;
        return $amf;
    }
    function allSpaceModels()
    {
        $avatar = new functions();

        $spaces = spaceModels::all();
        $count = count($spaces);
        $amf = new stdClass();
// $models = items::where('model_id', $items[0]["model_id"])->get();
// echo $models[0]["model_id"];
        $arr[] = array(
            "model_min_cl",
            "model_cid",
            "model_icon",
            "model_tags",
            "model_xplevelscript_id",
            "model_details",
            "model_default_edit_control",
            "model_category",
            "model_source",
            "model_default_access_control",
            "model_desc",
            "model_game_id",
            "model_id",
            "model_premium_only",
            "model_price_tokens",
            "model_type",
            "model_xpleveltype_id",
            "model_default_move_control",
            "model_xml",
            "model_allowed_count",
            "model_price",
            "model_min_xplevel"
        );

        for ($i = 0; $i < $count; $i++)
        {
           // $models = items::where('model_id', $items[$i]["model_id"])->get();
            $arr[] = array (
                $spaces[$i]["model_min_cl"],
                $spaces[$i]["model_cid"],
                $spaces[$i]["model_icon"],
                $spaces[$i]["model_tags"],
                $spaces[$i]["model_xplevelscript_id"],
                $spaces[$i]["model_details"],
                $spaces[$i]["model_default_edit_control"],
                $spaces[$i]["model_category"],
                $spaces[$i]["model_source"],
                $spaces[$i]["model_default_access_control"],
                $spaces[$i]["model_details"],
                $spaces[$i]["model_game_id"],
                $spaces[$i]["model_id"],
                $spaces[$i]["model_preium_only"],
                $spaces[$i]["model_price_tokens"],
                $spaces[$i]["model_type"],
                $spaces[$i]["model_xpleveltype_id"],
                $spaces[$i]["model_default_move_control"],
                $spaces[$i]["model_xml"],
                $spaces[$i]["model_allowed_count"],
                $spaces[$i]["model_price"],
                $spaces[$i]["model_min_xplevel"]
        );
       }
       return $arr;
    }



    function defaultSpaceModels($model_min_cl = "model_min_cl", $model_cid = "model_cid", $model_icon = "model_icon", $model_tags = "model_tags", $model_xplevelscript_id = "model_xplevelscript_id", $model_details = "model_details", $model_default_edit_control = "model_default_edit_control",$model_category = "model_category", $model_source = "model_source", $model_default_access_control = "model_default_access_control",  $model_desc = "model_desc", $model_game_id = "model_game_id", $model_id = "model_id", $model_premium_only = "model_premium_only", $model_price_tokens = "model_price_tokens", $model_type = "model_type", $model_xpleveltype_id = "model_xpleveltype_id", $model_default_move_control = "model_default_move_control", $model_xml = "model_xml", $model_allowed_count = "model_allowed_count", $model_price = "model_price", $model_min_xplevel = "model_min_xplevel")
    {
        return array
        (   $model_min_cl,
            $model_cid,
            $model_icon,
            $model_tags,
            $model_xplevelscript_id,
            $model_details,
            $model_default_edit_control,
            $model_category,
            $model_source,
            $model_default_access_control,
            $model_desc,
            $model_game_id,
            $model_id,
            $model_premium_only,
            $model_price_tokens,
            $model_type,
            $model_xpleveltype_id,
            $model_default_move_control,
            $model_xml,
            $model_allowed_count,
            $model_price,
            $model_min_xplevel
            );
    }
    function getAllSpaceModels()
    {
        return spaceModels::all()->toArray();
    }
    function viewForSaleSpaces($timeconfig, $skip, $take)
    {
        return new TypedRecordSetResult(  
            json_decode(avatarSpaces::where('user_id', 65)->where('forSale', 'Y')->get()->map(function ($space) {
                  return new SpacePanelItem($space);
            })));
    }
    function getForSale($skip, $take)
    {
        $avatar = new functions();

        $spaces = spaceModels::all();

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
                'modelId' => strval($spaces[$i]["model_id"]),
                'salePrice' => 0,
                'salePriceTokens' => 10,
                'lastPurchasedAmount' => 0,
                'lastPurchasedTokens' => 0,
                'forSale' => "Y",
                'spaceOwner' => $spaces[$i]["avatar_id"],
                'avatarId' => $avatar->getAvatar('avatar_id'),
                'avatarFName' =>$avatar->getAvatar('firstName'),
                'avatarLName' =>$avatar->getAvatar('lastName'),
                'avatarDetails' => null,
                'iconSource' => null,
                'thumbnailSource' => null,
                'spaceRoleAccess' => "1",
                'modelPrice' => $spaces[$i]["model_price"],





            );

        }
        return $arr;
    }
    function purchaseSpace($timeconfig, $param, $space_name, $spaceId)
    {
        $amf = new stdClass();
        $avatar =new functions();
        $user = Users::find(session('user'));

        // OLD CODE FOR SPACE SELECTOR!!!
        // if ($param != null)
        // {
            $avatarSpace = avatarSpaces::find($spaceId);
        $model_id = $avatarSpace->modelId;
        //     $model_id = $param;
            if ($space_name == null) {
                $model_name =
                $space_name = $avatar->getAvatar('firstName') . "'s " . spaceModels::where('model_id', $model_id)->pluck('model_details')->first();
            }
        // }
        $desc = spaceModels::where('model_id', $model_id)->pluck('model_desc')->first();

        // if ($user->serverIP != '127.0.0.1')
        //   $server = new SabreAMF_Client("https://".SITE_DOMAIN."/local/swds/gateway;jsessionid=".session('id')); // Set up the client object
        // else
        //   $server = new SabreAMF_Client("https://".SITE_DOMAIN."/localhost/swds/gateway;jsessionid=".session('id'));

            switch($avatarSpace->forSale)
            {
                case "Y": // Model Sale
                    $space = avatarSpaces::create
                    ([
                        'avatar_id' => $avatar->getAvatar('avatar_id'),
                        'user_id' => session('user'),
                        'name' => $space_name,
                        'modelId' => intval($model_id),
                        'config' => "",
                        'desc' => $desc
                    ]
                    );
                    break;
                case "I": // Instance Sale
                    // transfer instance to new owner
                    if ($avatarSpace->salePriceGold	> 0)
                        $avatarSpace->update(['lastPurchasedAmount' => $avatarSpace->salePriceGold]);
                    else
                        $avatarSpace->update(['lastPurchasedTokens' => $avatarSpace->salePriceTokens]);
                    $avatarSpace->avatar_id = $avatar->getAvatar('avatar_id');
                    $avatarSpace->user_id = session('user');
                    $avatarSpace->name = spaceModels::where('model_id', $model_id)->pluck('model_details')->first();
                    $avatarSpace->config = "";
                    $avatarSpace->desc = $desc;
                    $avatarSpace->forSale = "N";
                    $avatarSpace->salePriceGold = 0;
                    $avatarSpace->salePriceTokens = 0;
                    $avatarSpace->save();
                    // do a server refresh if people are in the space
                    $server->sendRequest('ds.refreshSpace', array($spaceId, $avatarSpace->forSale, $avatarSpace->salePriceGold, $avatarSpace->salePriceTokens));
                    
                    break;
            }
        
        // Home space ? true else false... update value
        $homeID = DB::getPdo()->lastInsertId();

        // if ($bool == true)
        // {
        //     Avatars::where('avatar_id', $avatar->getAvatar('avatar_id'))->update(['homeSpaceId' => $homeID]);

        // }

        $amf->homeId = $homeID;
        // $amf->space = $space;
        $amf->success =true;
        return $amf;
    }

    function searchSpacesAdv($timeconfig, $spaceName, $forSale, $modelID, $open, $aFN, $aLN, $ninstance)
    {
        $amf = new stdClass();
        // Search only by avatar name
        if ($spaceName == null && $forSale == false  && $aFN != null && $aLN != null )
        {
            $fullName = $aFN . $aLN;
            if ($ninstance == 0 && $open == false && $modelID == null)
            {
                $avatar = Avatars::where('fullName', 'LIKE', "%" . $fullName . "%")->where('nameInstance', $ninstance)->pluck('avatar_id');
                $amf->recordSet = $this->search(null, $avatar);
                $amf->startIndex = 0;
                $amf->maxLength = count($amf->recordSet);
                $amf->totalCount = count($amf->recordSet);
                $amf->success = true;
                return $amf;
            }

            if ($open == true && $modelID == null )
            {
                $avatar = Avatars::where('fullName', 'LIKE', "%" . $fullName . "%")->where('nameInstance', $ninstance)->pluck('avatar_id');
                $amf->recordSet = $this->search(null, $avatar);
                $amf->startIndex = 0;
                $amf->maxLength = count($amf->recordSet);
                $amf->totalCount = count($amf->recordSet);
                $amf->success = true;
                return $amf;
            }
          

            if ($modelID != null)
            {
                $avatar = Avatars::where('fullName', 'LIKE', "%" . $fullName . "%")->where('nameInstance', $ninstance)->pluck('avatar_id');
                $amf->recordSet = $this->search(null, $avatar, $open, $modelID);
                $amf->startIndex = 0;
                $amf->maxLength = count($amf->recordSet);
                $amf->totalCount = count($amf->recordSet);
                $amf->success = true;
                return $amf;
            }

           
            $avatar = Avatars::where('fullName', 'LIKE', "%" . $fullName . "%")->pluck('avatar_id');
            $amf->recordSet = $this->search(null, $avatar);
            $amf->startIndex = 0;
            $amf->maxLength = count($amf->recordSet);
            $amf->totalCount = count($amf->recordSet);
            $amf->success = true;
            return $amf;
        

            // var_dump($avatar);
            // $spaces = avatarSpaces::where('name','like', '%'.$key .'%')->get();

            // return $amf;
        }
        elseif($spaceName != null)
        {
            if ($open == false && $forSale == false && $modelID == null && $aFN == null && $aLN == null)
            {
                $amf->recordSet = $this->search($spaceName);
                $amf->startIndex = 0;
                $amf->maxLength = count($amf->recordSet);
                $amf->totalCount = count($amf->recordSet);
                $amf->success = true;
                return $amf;
            }
            if ($open == true && $forSale == false && $modelID == null && $aFN == null && $aLN == null)
            {
                $amf->recordSet = $this->search($spaceName, null, $open);
                $amf->startIndex = 0;
                $amf->maxLength = count($amf->recordSet);
                $amf->totalCount = count($amf->recordSet);
                $amf->success = true;
                return $amf;
            }
            if ($forSale == true && $modelID == null && $aFN == null && $aLN == null)
            {
                $amf->recordSet = $this->search($spaceName, null, $open, null, $forSale);
                $amf->startIndex = 0;
                $amf->maxLength = count($amf->recordSet);
                $amf->totalCount = count($amf->recordSet);
                $amf->success = true;
                return $amf;
            }
            if ($forSale == false && $modelID != null && $aFN == null && $aLN == null)
            {
                $amf->recordSet = $this->search($spaceName, null, $open, $modelID);
                $amf->startIndex = 0;
                $amf->maxLength = count($amf->recordSet);
                $amf->totalCount = count($amf->recordSet);
                $amf->success = true;
                return $amf;
            }
            if ($forSale == false && $modelID == null && $aFN != null && $aLN != null)
            {
                $fullName = $aFN . $aLN;
                $avatar = Avatars::where('fullName', 'LIKE', "%" . $fullName . "%")->pluck('avatar_id');
                $amf->recordSet = $this->search($spaceName, $avatar);
                $amf->startIndex = 0;
                $amf->maxLength = count($amf->recordSet);
                $amf->totalCount = count($amf->recordSet);
                $amf->success = true;
                return $amf;
            }
            if ($forSale == false && $modelID == null && $aFN == null && $aLN == null)
            {
                $amf->recordSet = $this->search($spaceName);
                $amf->startIndex = 0;
                $amf->maxLength = count($amf->recordSet);
                $amf->totalCount = count($amf->recordSet);
                $amf->success = true;
                return $amf;
            }
            if ($forSale == false && $modelID == null && $aFN != null && $aLN != null)
            {
                $fullName = $aFN . $aLN;
                $avatar = Avatars::where('fullName', 'LIKE', "%" . $fullName . "%")->pluck('avatar_id');
                $amf->recordSet = $this->search($spaceName, $avatar);
                $amf->startIndex = 0;
                $amf->maxLength = count($amf->recordSet);
                $amf->totalCount = count($amf->recordSet);
                $amf->success = true;
                return $amf;
            }
            
            $amf->recordSet = $this->search($spaceName);

            $amf->startIndex = 0;
            $amf->maxLength = count($amf->recordSet);
            $amf->totalCount = count($amf->recordSet);
            $amf->success = true;
            return $amf;
        }
        //search by modelId

        // Search only by avatar name & open only
        // else if ($spaceName == null && $forSale == false && $modelID == null && $open == true && $aFN != null && $aLN != null)


        // All Spaces - Search only by space name
        // if ($spaceName != null  && $modelID == null && $aFN == null && $aLN == null)
        // {
            // Search only by avatar name
            // if ($aFN != null && $aLN != null && $modelID == null)
            // {

            // }
        // }
        


    }

    function searchSpaces($timeconfig, $key)
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.TypedRecordSetResult";
        $amf->recordSet = $this->search($key);
        // $amf->startIndex = $skip;
        // $amf->maxLength = $take;
        $amf->totalCount = null;
        $amf->success = true;
        return $amf;
    }

    function search($key = null, $avatar_id = null, $open = null, $modelID = null)
    {
        $avatar = new functions();
        if ($key == null && $avatar_id != null && $open == null && $modelID == null)
        {
           $spaces = avatarSpaces::whereIn('avatar_id',  $avatar_id)->get();
        }
        elseif ($key != null && $avatar_id == null && $open == null && $modelID == null)
            $spaces = avatarSpaces::where('name','like', '%'.$key .'%')->get();
        elseif ($key == null && $avatar_id != null && $open == true && $modelID == null )
            $spaces = avatarSpaces::whereIn('avatar_id',  $avatar_id)->where('accessControl', 'O')->get();
        elseif ($key == null && $avatar_id != null && $modelID != null && $open == null)
            $spaces = avatarSpaces::whereIn('avatar_id',  $avatar_id)->where('modelId', $modelID)->get();
        elseif ($key == null && $avatar_id != null && $modelID != null && $open == true)
            $spaces = avatarSpaces::whereIn('avatar_id',  $avatar_id)->where('modelId', $modelID)->where('accessControl', 'O')->get();
      

        $amf = new stdClass();
        $count = count($spaces);
        //loop($count, $spaces);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $currentVisitors = onlineUsers::where('space_id', $spaces[$i]["id"])->where('online', true)->count();

            $arr[] = array (

            'id' => $spaces[$i]["id"],
            'spaceOwner' => $spaces[$i]["avatar_id"],
            'avatarId' => $spaces[$i]["avatar_id"],
            'avatarFName' =>$avatar->getAvatarByID($spaces[$i]["avatar_id"],'firstName'),
            'avatarLName' =>$avatar->getAvatarByID($spaces[$i]["avatar_id"],'lastName'),
            'avatarDetails' => null,
            'desc' => $spaces[$i]["name"],
            'details' => $spaces[$i]["desc"],
            'type' => "A",
            'forSale' => $spaces[$i]["forSale"],
            'salePrice' => $spaces[$i]["salePriceGold"],
            'salePriceTokens' => $spaces[$i]["salePriceTokens"],
            'iconSource' => $spaces[$i]["icon"],
            'accessControl' => $spaces[$i]["accessControl"],
            'modelId' => $spaces[$i]["modelId"],
            'spaceRoleAccess' => "0",
            'currentVisitors' => $currentVisitors,            'thumbnailSource' => $spaces[$i]['spaceThumbnailSource'],

        );

        }
        return $arr;
    }

    function updateSpaceSale($timeconfig, $spaceId, $saleType, $currency, $goldAmt, $tokenAmt)
    {
        //TODO: Implement this
        $user = Users::find(session('user'));
        //update avatarSpaces
        $space = avatarSpaces::find($spaceId);
        $space->forSale = $saleType;
        if ($currency == 'gold')
            $space->salePriceGold = $goldAmt;
        else
            $space->salePriceTokens = $tokenAmt;
        $space->save();

        // initialize the rtmp server
        // if ($user->serverIP != '127.0.0.1')
        //   $server = new SabreAMF_Client("https://".SITE_DOMAIN."/local/swds/gateway;jsessionid=".session('id')); // Set up the client object
        // else
        //   $server = new SabreAMF_Client("https://".SITE_DOMAIN."/localhost/swds/gateway;jsessionid=".session('id'));

        //   $server->sendRequest('ds.updateSpaceSale', array($spaceId, $saleType, $currency, $goldAmt, $tokenAmt, session('avatar')));

        return new ServiceResult;
    }
}

<?php
include ('functions.php');

use App\Models\avatarSpaces;
use App\Models\onlineUsers;
use App\Models\Users;
// use App\Http\Services\functions;

class ls 
{
    function lookupSpace($timeconfig, $spaceID, $bool)
    {
        // if ($spaceID == str)
        $avatarSpace = avatarSpaces::where('id', $spaceID)->exists();

        $alias = avatarSpaces::where('alias', $spaceID)->exists();
        // var_dump($avatarSpace);
        if ($avatarSpace == false && $alias == true) {
            // $avatarSpace = $alias[0];
            // echo 'we are here';
            $avatarSpace = avatarSpaces::where('alias', $spaceID)->first();
           

        }
        else{
            $avatarSpace = avatarSpaces::where('id', $spaceID)->first();
        }

        // $online = onlineUsers::where('avatar_id', session('avatar'))->exists();

        // if ($online) 
        // {
        //     onlineUsers::where('avatar_id', session('avatar'))->update(['space_id' => $spaceID]);
        // }
        // else
        // {
        //     onlineUsers::create(['avatar_id' => session('avatar'), 'space_id' => $spaceID]);
        // }

        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.entity.space.result.LookupSpaceResult";
        $ret->locations = array($this->getLocation($spaceID, $alias));
        $ret->ownerId = strval($avatarSpace['user_id']);
        $ret->accessControl = $avatarSpace['accessControl'];
        $ret->spaceDesc = $avatarSpace['name'];
        $ret->spaceId =  (string) $spaceID;
        session(['space' => $spaceID]);
        $ret->success = true;
        return $ret;
    }

    function getLocation($id, $alias = false)
    {
        $spaceInfo = avatarSpaces::where('id', $id)->first();

        $currentVisitors = onlineUsers::where('space_id', $spaceInfo->id)->where('online',true)->count();
        if ($spaceInfo->instanceable && $currentVisitors >= $spaceInfo->maxVisitors)
        // create a new instance of the space
        {}

        if ($alias == false)
        {
            $avatarSpace = avatarSpaces::where('id', $id)->get()[0];
        }
        else{
            $avatarSpace = avatarSpaces::where('alias', $id)->get()[0];
        }
        $user = Users::where('id', session('user'))->get()[0];

        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;

        $ret->$explicitTypeField = "com.smallworlds.service.result.server.Location";
        $ret->visitors = (float) $currentVisitors;
        $ret->instanceId = (float) 0;
        $ret->maxVisitors = (float) $avatarSpace['maxVisitors'];

        $ret->hostname = $user['serverIP'];
        $ret->starve = false;
        return $ret;
    }
}

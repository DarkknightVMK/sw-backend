<?php

use App\Models\Avatars;
use App\Models\Users;
use App\result\CompressionUtil;
use App\Models\avatar_xp;
use App\Models\avatarSpaces;
use App\Models\onlineUsers;

class avatar
{
    public function getAvatar($aid, $json = null)
    {
        $avatar_json = Avatars::where('avatar_id', $aid)->get();
        $avatar_json = $avatar_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_json, $json2decode);
        $avatar = json_decode($json2decode[0][0], true);
        if ($json == null)
            return $avatar;
        return $avatar[$json];
    }
    
    public function getAvatarXP($aid, $type, $json)
    {
        $avatar_xp_json = avatar_xp::where('avatar_id', $aid)->where('xpleveltype', $type)->get();
        $avatar_xp_json = $avatar_xp_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_xp_json, $json2decode);
        $avatar_xp = json_decode($json2decode[0][0], true);
        if ($json == null)
            return $avatar_xp;
        return $avatar_xp[$json];
    }

    function findAvatar($timeconfig, $aid)
    {
        $amf = new stdClass();
        if ( strlen($aid) == 32)
        {
            $avatar = Avatars::where('avatar_id', $aid)->get()->first();
        }
        else
        {
            $avatar = Avatars::where('fullName', 'LIKE', $aid .'%')->get()->first();
            // var_dump($avatar);
        }
        $amf->id = $avatar->avatar_id;
        $amf->desc = $avatar->firstName . ' ' . $avatar->lastName;
        $amf->success=true;
        return $amf;
    }

    function searchAvatars($timeconfig, $string, $aid, $uid, $fname, $lname, $instance, $online, $active)
    {
        $amf = new stdClass();
        $amf->recordSet = $this->search($aid, $uid, $fname, $lname);
        $amf->success=true;
        return $amf;
    }

    function getAvatarDetails($timeconfig, $aid)
    {

        $amf = new stdClass();
        $amf->id = $aid;
        $amf->ownerId = $this->getAvatar($aid, 'owner_id');
        $amf->indexed = true;
        $amf->fName = $this->getAvatar($aid, 'firstName');
        $amf->lName = $this->getAvatar($aid,'lastName');
        $amf->nameInstance = $this->getAvatar($aid,'nameInstance');
        $amf->snapshotUrl = $this->getAvatar($aid,'snapUrl');
        $amf->thumbUrl = $this->getAvatar($aid,'thumbUrl');
        $amf->online = onlineUsers::where('avatar_id', $aid)->where('online',true)->exists();
        $amf->currentSpaceId = (onlineUsers::where('avatar_id', $aid)->where('online',true)->exists()) ? onlineUsers::where('avatar_id', $aid)->where('online',true)->get()->first()->space_id : null;
        $amf->currentSpaceDesc = (onlineUsers::where('avatar_id', $aid)->where('online',true)->exists()) ? avatarSpaces::where('id', onlineUsers::where('avatar_id', $aid)->where('online',true)->get()->first()->space_id)->get()->first()->name : null;
        $amf->active = true;
        $amf->motto= "hi";

        // XP
        $amf->artistLevel = $this->getAvatarXP($aid, 1, 'level');
        $amf->artistXp = $this->getAvatarXP($aid, 1, 'xp');
        $amf->explorerLevel = $this->getAvatarXP($aid, 2, 'level');
        $amf->explorerXp = $this->getAvatarXP($aid, 2, 'xp');
        $amf->gamerLevel = $this->getAvatarXP($aid, 3, 'level');
        $amf->gamerXp = $this->getAvatarXP($aid, 3, 'xp');
        $amf->socialLevel = $this->getAvatarXP($aid, 4, 'level');
        $amf->socialXp = $this->getAvatarXP($aid, 4, 'xp');
        $amf->arenaLevel = $this->getAvatarXP($aid, 5, 'level');
        $amf->arenaXp = $this->getAvatarXP($aid, 5, 'xp');
        $amf->farmerLevel = $this->getAvatarXP($aid, 6, 'level');
        $amf->farmerXp = $this->getAvatarXP($aid, 6, 'xp');
        $amf->craftingLevel = $this->getAvatarXP($aid, 7, 'level');
        $amf->craftingXp = $this->getAvatarXP($aid, 7, 'xp');
        $amf->primaryLevel = $this->getAvatarXP($aid, 8, 'level');
        $amf->primaryXp = $this->getAvatarXP($aid, 8, 'xp');
        $amf->configString = CompressionUtil::decompress($this->getAvatar($aid,'config'));
        $amf->success=true;
        return $amf;
    }

    function search($aid, $uid, $fname, $lname)
    {

        // $avatar = new functions();
        if ($aid == null)
        {
            $user = Users::where('id', $uid )->first();
            $avatar = Avatars::where('owner_id', $uid)->get();

        }
        elseif ($uid == null)
        {
            $avatar = Avatars::where('avatar_id', $aid)->get();
            $user = Users::where('id', $avatar[0]->owner_id)->first();
        }
  
        else
        {
            $avatar = Avatars::where('avatar_id', $aid )->get();
            $user = Users::where('id', $uid )->get();
        }
            // $aid = $uid;

        

        $ret = new stdClass();
        $count = count($avatar);
        //loop($count, $spaces);
        // var_dump($count);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $arr[] = array (

            'id' => $avatar[$i]["avatar_id"],

            'avatar' => array
            (
              'id' => strval($avatar[$i]["avatar_id"]),
              'nameInstance' => $avatar[$i]['nameInstance'],
              'lastName' => $avatar[$i]['lastName'],
              'firstName' => $avatar[$i]['firstName'],
              'hasPet' => false,
              'isOnline' => onlineUsers::where('avatar_id', $avatar[$i]["avatar_id"])->where('online',true)->exists(),
              'isDefault' => true,
            //   'avatarId' => $avatar->getAvatar('avatar_id'),
            //   'avatarFName' =>$avatar->getAvatar('firstName'),
            //   'avatarLName' =>$avatar->getAvatar('lastName'),
            //   'avatarDetails' => null,
            //   'desc' => $spaces[$i]["name"],
            //   'details' => $spaces[$i]["desc"],
            //   'type' => "A",
            //   'forSale' => "N",
            //   'iconSource' => $spaces[$i]["icon"],
            //   'accessControl' => $spaces[$i]["accessControl"],
            //   'modelId' => strval($spaces[$i]["modelId"]),
            //   'spaceRoleAccess' => "0",
            //   'currentVisitors' => $spaces[$i]['currentVisitors'],
            //   "instanceId" => 0
            ),

            'owner' => array   // UserSMIINFO
            (

              'id' => strval($user->id),
              'firstName' => $user->firstName,
              'lastName' => $user->lastName,
              'emailAddress' => $user->email,
              'payingUser' => true,
            //   'warningMessage' => "",
              'buttonColor' => "",
              'buttonReason' => ""
            ),

            'currentSpace' => array
            (
              'id' => (onlineUsers::where('avatar_id', $avatar[$i]["avatar_id"])->where('online',true)->exists()) ? strval(onlineUsers::where('avatar_id', $avatar[$i]["avatar_id"])->where('online',true)->get()->first()->space_id) : null,
                'desc' => (onlineUsers::where('avatar_id', $avatar[$i]["avatar_id"])->where('online',true)->exists()) ? avatarSpaces::where('id', onlineUsers::where('avatar_id', $avatar[$i]["avatar_id"])->where('online',true)->get()->first()->space_id)->get()->first()->name : null,
                'details' => (onlineUsers::where('avatar_id', $avatar[$i]["avatar_id"])->where('online',true)->exists()) ? avatarSpaces::where('id', onlineUsers::where('avatar_id', $avatar[$i]["avatar_id"])->where('online',true)->get()->first()->space_id)->get()->first()->desc : null,
                'instanceId' => 1,
            ),

            'active' => true,
            'hasPet' => false,
            
        );

        }
        return $arr;
    }
}

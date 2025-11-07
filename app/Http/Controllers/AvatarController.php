<?php

namespace App\Http\Controllers;
require_once(__DIR__ . '../../../../resources/php/config.php');

use App\Models\Avatars;
use App\Models\userGroups;
use App\Models\Users;
use App\Models\avatarSpaces;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use App\Models\sessions;
use App\Models\onlineUsers;
use App\Models\pets;
use App\Models\avatar_xp;
use App\Models\avatarItems;
use App\Models\avatarWearing;
use App\Models\avatarArtifacts;
use App\Models\avatarMissionKeys;
use App\Models\avatarMissions;
use App\Models\avatar_outfits;
use App\Models\avatarFriends;
use App\Http\Services\functions;
use App\result\spintowin\InitStateResult;
use App\Models\ai;
use App\Http\Controllers\Utils;
use Illuminate\Support\Facades\Storage;


class AvatarController extends Controller
{
    
    public function online()
    {
        $online = onlineUsers::where('online',true)->where('local',false)->get();
        $overall = 0;
        $overall = $online->count();
        return \response(
            [
                $overall
            ],
            200);
    }

    public function nameCheck($request)
    {
        function ordinal($number) {
            $ends = array('th','st','nd','rd','th','th','th','th','th','th');
            if ((($number % 100) >= 11) && (($number%100) <= 13))
                return $number. 'th';
            else
                return $number. $ends[$number % 10];
        }
        if (Avatars::where('firstName', $request['firstName'])->where('lastName', $request['lastName'])->get() != '[]')
        {
            $nameInstance = count(Avatars::where('firstName', $request['firstName'])->where('lastName', $request['lastName'])->get()) + 1;
            if ($nameInstance > 1)
                return $request['firstName'] . " " . $request['lastName'] . " the ". ordinal($nameInstance);
        }
        return true;
    }

    public function getFriends($avatarId)
    {
        $friends = avatarFriends::where('avatar_id', $avatarId)->get();
        $friendIds = [];
        
        foreach ($friends as $friend) {
            $friendIds[] = $friend;
        }
        // for each friendid I want to add their thumbUrl and fullName
        foreach ($friendIds as $key => $friend) {
            $friendAvatar = Avatars::where('avatar_id', $friend->friend_id)->first();
            if ($friendAvatar) {
                $friendIds[$key]->thumbUrl = $friendAvatar->thumbUrl;
                $friendIds[$key]->firstName = ucfirst($friendAvatar->firstName);
                $friendIds[$key]->lastName = ucfirst($friendAvatar->lastName);
            } else {
                // If the friend's avatar doesn't exist, remove them from the list
                unset($friendIds[$key]);
            }
        }
        // are they online ? y/n if no show last online time
        foreach ($friendIds as $key => $friend) {
            $onlineEntry = onlineUsers::where('avatar_id', $friend->friend_id)->where('online', true)->first();
            if ($onlineEntry) {
                $friendIds[$key]->online = true;
                $friendIds[$key]->lastOnline = null;
            } else {
                $friendIds[$key]->online = false;
                $lastOnlineEntry = onlineUsers::where('avatar_id', $friend->friend_id)->orderBy('updated_at', 'desc')->first();
                $friendIds[$key]->lastOnline = $lastOnlineEntry ? $lastOnlineEntry->lastOnline : null;
            }
        }
        
        return $friendIds;
    }

    public function avatarDetails(Request $request)
    {
        $aid = $request->aid;
        // Please note this is not protected, any loggedIn user can get details of any avatar
        // TODO: Auth certain results of the getAvatarResponse function
        if (Avatars::where('avatar_id', $aid)->exists())
        {
            return \response(
                [
                    'data' => $this->getAvatarResponse($aid, true, false),
                    'success' => true
                ],
                200);
        }
        else
        {
            return \response(
                [
                    'errorCode' => 600,
                    'data' => null,
                    'success' => false
                ],
                200);
        }
    }

    public function makeDefaultAvatar(Request $request)
    {
        $aid = $request->aid;
        $user = Auth::user();
        $avatar = Avatars::where('avatar_id', $aid)->first();

        if (Avatars::where('owner_id', $user->id)->where('avatar_id', $aid)->exists())
        {
            //update user
            $user = Users::find($user->id);
            $user->defaultAvatar = $aid;
            $user->save();
            return \response([
                'avatar' => $this->getAvatarResponse($aid),
                'success' => true
            ]
            , 200);
            
        }
        return \response(
            [
                'errorCode' => 0,
                'data' => null,
                'success' => true
            ], 200);

        //return true
        
    }
    public function deleteAvatar(Request $request)
    {
        $aid = $request->aid;
        $user = Auth::user();
        // make sure the aid is not the default avatar
        if ($user->defaultAvatar == $aid)
        {
            return \response(
                [
                    'errorCode' => 0,
                    'data' => null,
                    'success' => false
                ], 200);
        }
        if (Avatars::where('owner_id', $user->id)->where('avatar_id', $aid)->exists())
        {
            //update user
            $user = Users::find($user->id);
            $user->choosenAvatar = null;
            $user->save();
            // delete xp
            $xp = avatar_xp::where('avatar_id', $aid)->get();
            if ($xp != null)
            {
                foreach ($xp as $x)
                {
                    $x->delete();
                }
            }
            // delete avatar wearing
            $avatarWearing = avatarWearing::where('avatar_id', $aid)->get();
            if ($avatarWearing != null)
            {
                foreach ($avatarWearing as $a)
                {
                    $a->delete();
                }
            }
            // delete avatar pet
            $avatarPet = pets::where('ownerId', $aid)->get();
            if ($avatarPet != null)
            {
                foreach ($avatarPet as $a)
                {
                    $a->delete();
                }
            }
            //delete avatar outfits
            $avatarOutfit = avatar_outfits::where('avatar_id', $aid)->get();
            if ($avatarOutfit != null)
            {
                foreach ($avatarOutfit as $a)
                {
                    $a->delete();
                }
            }
            // delete avatar missions
            $avatarMission = avatarMissions::where('avatar_id', $aid)->get();
            if ($avatarMission != null)
            {
                foreach ($avatarMission as $a)
                {
                    $a->delete();
                }
            }
            //change avatar_id to null on avatar items
            $avatarItems = avatarItems::where('avatar_id', $aid)->get();
            if ($avatarItems != null)
            {
                foreach ($avatarItems as $a)
                {
                    $a->avatar_id = null;
                    $a->save();
                }
            }
            // delete avatar arifacts
            $avatarArtifact = avatarArtifacts::where('avatar_id', $aid)->get();
            if ($avatarArtifact != null)
            {
                foreach ($avatarArtifact as $a)
                {
                    $a->delete();
                }
            }
            // delete avatar mission keys
            $avatarMissionKey = avatarMissionKeys::where('avatar_id', $aid)->get();
            if ($avatarMissionKey != null)
            {
                foreach ($avatarMissionKey as $a)
                {
                    $a->delete();
                }
            }
            // delete online users
            $onlineUsers = onlineUsers::where('avatar_id', $aid)->get();
            if ($onlineUsers != null)
            {
                foreach ($onlineUsers as $a)
                {
                    $a->delete();
                }
            }
            // delete avatar spaces
            $avatarSpaces = avatarSpaces::where('avatar_id', $aid)->get();
            if ($avatarSpaces != null)
            {
                foreach ($avatarSpaces as $a)
                {
                    $a->delete();
                }
            }
            // delete avatar
            $avatar = Avatars::where('owner_id', $user->id)->where('avatar_id', $aid)->first();
            $avatar->delete();
            return \response(
                'true'
            , 200);
            
        }
        return \response(
            [
                'errorCode' => 0,
                'data' => null,
                'success' => true
            ], 200);

        //return true
        
    }

    public function update_avatar(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $swsid = $request->header('SWSID');
        $session = sessions::where('SWSID', $swsid)->get()->first();
        if ($session->user_id != $user_id)
        {
            return \response(
                [
                    'error' => 'Session expired'
                ],
                401);
        }
        $user = Users::where('id', $user_id)->first();
        $secondaryGroups = explode(',', $user->secondaryGroupIds);
        $primaryGroup = $user->primaryGroupId;
        $admin = (in_array(2, $secondaryGroups) || $primaryGroup == 2);
        $fields = $request->validate([
            'id' => 'required|string|max:32',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'configXML' => 'required|string',
            'gender' => 'required|string',
            'nameInstance' => 'required|integer',
        ]);
        if (!$admin)
        {
            $avatar = Avatars::where('avatar_id', $fields['id'])->first();
            if ($avatar->owner_id != $user_id)
            {
                return \response(
                    [
                        'error' => 'You can only update your own avatars'
                    ],
                    401);
            }
        }
        if (!$fields['gender'] === 'male' || !$fields['gender'] === 'female')
        {
            return \response(
                [
                    'error' => 'Invalid gender'
                ],
                400);
        }

        $firstName = $fields['firstName'];
        $lastName = $fields['lastName'];
        $avatar_id = $fields['id'];
        $nameCheck = ['firstName' => $firstName, 'lastName' => $lastName];
        $avatar = Avatars::where('avatar_id', $avatar_id)->first();
        if ($avatar->firstName === $firstName && $avatar->lastName === $lastName &&  $avatar->gender === $fields['gender'] && $avatar->nameInstance === $fields['nameInstance'])
        {
            return \response(
                [
                    'error' => 'No changes made'
                ],
                400);
        }
        if ($avatar->firstName != $firstName || $avatar->lastName != $lastName )
        {
            $name = AvatarController::nameCheck($nameCheck);
            $nameInstance = 1;
            if (is_string($name))
            {
                $nameInstance = preg_replace('/[^0-9]/', '', $name);
                $avatar->nameInstance = $nameInstance;
            }
           
            $avatar->nameInstance = $nameInstance;
            $avatar->firstName = $firstName;
            $avatar->lastName = $lastName;
            $avatar->gender = $fields['gender'];
            $avatar->save();
            return \response(
                [
                    'data' => [
                        'firstName' => $avatar->firstName,
                        'lastName' => $avatar->lastName,
                        'gender' => $avatar->gender,
                        'nameInstance' => $avatar->nameInstance,
                        'id' => $avatar->avatar_id,
                        'configXML' => Utils::decompressStr($avatar->config),
                    ],
                    'success' => 'true'
                ],
                200);
        }
        else {
            $avatar->gender = $fields['gender'];
            $configXML = $fields['configXML'];
            // if not a valid xml, return error
            if (strpos($configXML, '<config>') === false) {
                return \response(
                    [
                        'error' => 'Invalid XML'
                    ],
                    400);
            }
            $pattern = '/<avatar\s+gender="(male|female)"/';
            // <option id="GenderOptions" sel="Male"/> 
            $pattern2 = '/<option\s+id="GenderOptions"\s+sel="(male|female)"/';
    
            $replacement = '<avatar gender="' . $fields['gender'] . '"';
            $replacement2 = '<option id="GenderOptions" sel="' . $fields['gender'] . '"';
            $configXML = preg_replace($pattern, $replacement, $configXML);
            $configXML = preg_replace($pattern2, $replacement2, $configXML);
            $avatar->config = Utils::compressStr($configXML);
            $avatar->save();
            return \response(   
                [
                    'data' => [
                        'firstName' => $avatar->firstName,
                        'lastName' => $avatar->lastName,
                        'gender' => $avatar->gender,
                        'nameInstance' => $avatar->nameInstance,
                        'id' => $avatar->avatar_id,
                        'configXML' => Utils::decompressStr($avatar->config),
                    ],
                    'success' => 'true'
                ],
                200);
        }
    }

    public function apiOnline()
    {
        return onlineUsers::all();
    }

    public function worldOnline()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $connected = @fsockopen($ip, 5080);
        if ($connected){
            fclose($connected);
            return 
            \response(
                [
                    'online' => true
                ],
                200);
        }
        return  \response(
            [
                'online' => false
            ],
            200);
    }

    public function avis()
    {
        return Avatars::all();
    }

    public function aviID(Request $request)
    {
        $avi = $request->aid;
        return Avatars::find($avi);
    }

    public function randomHead(Request $request){
        if ($request->email != null ){
            $email = $request->email;
            $user = Users::where('email', $email)->first();
            $avatar = $user->defaultAvatar;
            $avatar = Avatars::where('avatar_id', $avatar)->first();
            $head = $avatar->thumbUrl;
            return \response(
                [
                    'url' => $head
                ],
                200);
        }
        else{
            $avatar = Avatars::inRandomOrder()->first();
            $head = $avatar->thumbUrl;
            return \response(
                [
                    'url' => $head
                ],
                200);
        }
    }

    public function updateFace(Request $request)
    {
        $avatar_id = $request->aid;
        if (Avatars::find($avatar_id)->exists() || ai::where('avatar_id', $avatar_id)->exists())
        {
            $head =  $request->head;
            $thumb = $request->thumb;
            $snap = $request->snap;
            $config = $request->config;
            $avatar_url = \Config::get('custom.avatars_url');

            if (Avatars::where('avatar_id',$avatar_id)->exists())
            {
                $postfix = Avatars::where('avatar_id', $avatar_id)->pluck('headPostfix')->first();
                if (Storage::disk('avatars')->exists($avatar_id . $postfix . ".png") === true)
                {
                    Storage::disk('avatars')->delete($avatar_id . $postfix . ".png");
                    Storage::disk('avatars')->delete($avatar_id . $postfix . "_thumb.png");
                    Storage::disk('avatars')->delete($avatar_id . $postfix . "_snap.png");
                }
                $filename = Utils::generateRandomString();
                Avatars::where('avatar_id', $avatar_id)->update(['headPostfix' => $filename, 'snapshotPostfix' => $filename, 'thumbUrl' => $avatar_url .'/'. $avatar_id. $filename. '_thumb.png', 'snapUrl' => $avatar_url .'/' . $avatar_id. $filename. '_snap.png']);
                $this->uploadFile($head, $avatar_id . $filename . ".png");
                $this->uploadFile($thumb, $avatar_id . $filename . "_thumb.png");
                $this->uploadFile($snap, $avatar_id . $filename . "_snap.png");
                Avatars::where('avatar_id', $avatar_id)->update(['config' => $config]);
            }
            elseif (ai::where('avatar_id', $avatar_id)->exists())
            {
                $postfix = ai::where('avatar_id', $avatar_id)->pluck('headPostfix')->first();
                if (Storage::disk('avatars')->exists($avatar_id . $postfix . ".png") === true)
                {
                    Storage::disk('avatars')->delete($avatar_id . $postfix . ".png");
                    Storage::disk('avatars')->delete($avatar_id . $postfix . "_thumb.png");
                    Storage::disk('avatars')->delete($avatar_id . $postfix . "_snap.png");
                }
                $filename = Utils::generateRandomString();
                ai::where('avatar_id', $avatar_id)->update(['headPostfix' => $filename, 'snapshotPostfix' => $filename, 'thumbUrl' => $avatar_url .'/'. $avatar_id. $filename. '_thumb.png', 'snapshotUrl' => $avatar_url .'/' . $avatar_id. $filename. '_snap.png']);
                $this->uploadFile($head, $avatar_id . $filename . ".png");
                $this->uploadFile($thumb, $avatar_id . $filename . "_thumb.png");
                $this->uploadFile($snap, $avatar_id . $filename . "_snap.png");
                ai::where('avatar_id', $avatar_id)->update(['configString' => $config]);
            }

            return \response(
                [
                    'success' => true
                ],
                200);
        }
        else
        {
            return \response(
                [
                    'success' => false
                ],
                200);
        }
    }

    public function uploadFile($file, $name)
    {
        $file = hex2bin($file);
        $str = amf_encode($file, AMF_CLASS_MAPPING);
        Storage::disk('avatars')->put( $name,  amf_decode($str));
    }

    public function pet(Request $request)
    {
        $id = $request->id;

        if (pets::where('ownerId', $id)->exists()) {
            $pet = pets::where('ownerId', $id)->first();
            return \response(
                [
                    'status' => false,
                    'snapUrl' => "https://avatars.".SITE_DOMAIN."/".$pet->stringId . $pet->snapshotPostfix . "_snap.png",
                ], 200);
        }
        else{
            return \response(
                [
                    'errorCode' => 600,
                    'data' => null,
                    'success' => false
                ],
                200);
            }
        return \response(
            [
                
            ], 404
        );
    }

    public function petDetail(Request $request)
    {
        $id = $request->id;
        if (pets::where('ownerId', $id)->exists()) {
            $pet = pets::where('ownerId', $id)->first();
            return \response(
                [
                    'id' => $pet['id'],
                    'stringid' => $pet['stringId'],
                    'postfix' => $pet['headPostfix'],
                    'ownerid' => $pet['ownerId'],
                    'name' => $pet['name'],
                    'desc' => $pet['motto'],
                    'snapshot' => $pet['snapshotUrl'],
                    'thumbnail' => $pet['thumbUrl'],
                    'head' => $pet['headUrl'],
                    'config' => $pet['configString'],
                    'memory' => $pet['memoryString'],
                ], 200);
        }
        else{
            return \response(
                [
                    'errorCode' => 600,
                    'data' => null,
                    'success' => false
                ],
                200);
            }
    }

    public function updatePetDetails(Request $request)
    {
        $id = $request->id;
        if (pets::where('stringId', $id)->exists()) {
            $pet = pets::where('stringId', $id)->first();
            for ($i = 0; $i < count($request->all()); $i++)
            {
                $pet[$request->all()[$i]] = $request->all()[$i];
            }
            $pet->save();
            
            return \response(
                [
                    $pet->toArray()
                ], 200);
        }
        else{
            return \response(
                [
                    'errorCode' => 600,
                    'data' => null,
                    'success' => false
                ],
                200);
            }
    }

    public function checkPlant(Request $request)
    {
        return \response(
            [
                'errorCode' => 0,
                'data' => null,
                'success' => true
            ],
            200);
    }

    public function balance(Request $request)
    {
        $user = Auth::user();
        return \response(
            [
                'errorCode' => 0,
                'data' => [
                    'goldBalance' => $user->goldBalance,
                    'tokenBalance' => $user->tokenBalance,
                ],
                'success' => true
            ],
            200);
    }

    public function checkSpin(Request $request)
    {
        $user =  Auth::user();
        return \response(
            [
                'coolDownTime' => InitStateResult::timeRemaining($user->cooldownTimeRemaining),
                'data' => null,
                'success' => true
            ],
            200);
    }

    public static function ordinal($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13))
            return $number. 'th';
        else
            return $number. $ends[$number % 10];
    }

    private function calcXP($level, $levelType)
    {
        switch ($levelType) {
            case 1: case 2: case 3: case 4: case 5: {
                if ($level < 10)
                    return $level > 1 ? $level * 60 : 0;
                if ($level < 20) {
                    return 600 + ($level - 10) * 60;
                }
                if ($level < 30) {
                    return 1200 + ($level - 20) * 120;
                }
                if ($level < 40) {
                    return 2400 + ($level - 30) * 240;
                }
                if ($level < 50) {
                    return 4800 + ($level - 40) * 480;
                }
                if ($level <= 500) {
                    return 9600 + ($level - 50) * 480;
                }
                if ($level == 700) {
                    return 100000000;
                }
                if ($level == 800) {
                    return 200000000;
                }
                if ($level == 900) {
                    return 300000000;
                }
            }
            case 6: case 7:{ return (float)(floor(4 * pow($level - 1,2)));}
            case 8 : {
                return (float) (round(0.1 * 4 * pow($level - 1, 2.25)) * 10 + 60 * ($level - 1));
            }
        }
        return 0;
    }

    private function getMySpaces($user)
    {
        $spaces = avatarSpaces::where('user_id', $user->id)->get();
        $arr = [];
        
        foreach ($spaces as $space) {
            $arr[] = [
                'spaceId' => $space->id,
                'desc' => $space->name
            ];
        }
        
        return $arr;
    }

    private function getPermissions($user)
    {
        $primaryGroup = $user->primaryGroupId;
        
        if ($primaryGroup != null) {
            $group = userGroups::where('id', $primaryGroup)->pluck('permissionId');
            $splittedNumbers = explode(", ", $group[0]);
            return $splittedNumbers;
        }
        
        return [];
    }

    private function getActiveAvis()
    {
        $user = Auth::user();
        
        if (!Avatars::where('owner_id', $user->id)->exists()) {
            return [strval($user->defaultAvatar)];
        }
        
        $avatars = Avatars::where('owner_id', $user->id)->get();
        $avatar_ids = $avatars->pluck('avatar_id')->toArray();
        
        $defaultAvatar = $user->defaultAvatar;
        if (in_array($defaultAvatar, $avatar_ids)) {
            $defaultAvatarIndex = array_search($defaultAvatar, $avatar_ids);
            unset($avatar_ids[$defaultAvatarIndex]);
            array_unshift($avatar_ids, $defaultAvatar);
        }
        
        $result = [];
        foreach ($avatar_ids as $avatarId) {
            $avatar = Avatars::where('avatar_id', $avatarId)->first();
            if (!$avatar) continue;
            
            $result[] = [
                'id' => strval($avatar->avatar_id),
                'name' => strtolower($avatar->fullName),
                'firstName' => ucfirst($avatar->firstName),
                'lastName' => ucfirst($avatar->lastName),
                'nameInstance' => $avatar->nameInstance,
                'fullName' => $this->formatFullName($avatar),
                'snapUrl' => $avatar->snapUrl,
                'thumbUrl' => $avatar->thumbUrl,
                'configXML' => Utils::decompressStr($avatar->config),
                'pet' => $this->getPetData($avatarId),
            ];
        }
        
        return $result;
    }

    private function formatFullName($avatar)
    {
        $firstName = ucfirst($avatar->firstName);
        $lastName = ucfirst($avatar->lastName);
        
        if ($avatar->nameInstance == 1) {
            return "{$firstName} {$lastName}";
        }
        
        return "{$firstName} {$lastName} the " . self::ordinal($avatar->nameInstance);
    }

    /**
     * Check if an avatar has an active pet to take along
     * 
     * @param string $avatarId
     * @return boolean
     */
    private function hasTakePet($avatarId)
    {
        if (pets::where('ownerId', $avatarId)->exists()) {
            $pet = pets::where('ownerId', $avatarId)->first();
            return $pet->active == 1;
        }
        return false;
    }

    /**
     * Get pet type based on config string
     * 
     * @param string $avatarId
     * @return string|null
     */
    private function getPetType($avatarId)
    {
        if (!pets::where('ownerId', $avatarId)->exists()) {
            return null;
        }
        
        $pet = pets::where('ownerId', $avatarId)->first();
        $configString = Utils::decompressStr($pet->configString);
        
        if (str_contains($configString, 'pets/characters/dogs')) {
            return 'dog';
        } elseif (str_contains($configString, 'pets/characters/cats')) {
            return 'cat';
        }
        
        return null;
    }

    /**
     * Get pet data for an avatar
     * 
     * @param string $avatarId
     * @return array|null
     */
    private function getPetData($avatarId)
    {
        if (!pets::where('ownerId', $avatarId)->exists()) {
            return null;
        }
        
        $pet = pets::where('ownerId', $avatarId)->first();
        
        return [
            'status' => true,
            'snapUrl' => $pet->snapshotUrl, 
            'takePet' => $this->hasTakePet($avatarId),
            'petType' => $this->getPetType($avatarId)
        ];
    }

    /**
     * Get full avatar details for responses
     * 
     * @param string $avatarId
     * @param bool $enHeader
     * @param bool $experiment
     * @return array
     */
    private function getAvatarResponse($avatarId, $enHeader = false, $experiment = false)
    {
        $avatar = Avatars::where('avatar_id', $avatarId)->first();
        if (!$avatar) {
            return null;
        }
        
        // Get XP data for all types
        $xpData = [];
        $xpTypes = [
            1 => 'artist', 
            2 => 'explorer',
            3 => 'gamer',
            4 => 'social',
            5 => 'arena',
            6 => 'farmer',
            7 => 'crafter',
            8 => 'primary'
        ];
        
        foreach ($xpTypes as $typeId => $typeName) {
            $xp = avatar_xp::where('avatar_id', $avatarId)
                          ->where('xpleveltype', $typeId)
                          ->first();
            
            if ($xp) {
                $isPrimary = $typeId === 8;
                $xpData[$typeName] = [
                    'level' => $isPrimary ? strval($xp->level) : $xp->level,
                    'xp' => $isPrimary ? strval($xp->xp) : $xp->xp,
                    'levelXP' => $this->calcXP($xp->level, $typeId),
                ];
            }
        }
        
        // Build response
        $response = [
            'id' => strval($avatar->avatar_id),
            'name' => strtolower($avatar->fullName),
            'firstName' => ucfirst($avatar->firstName),
            'lastName' => ucfirst($avatar->lastName),
            'nameInstance' => $avatar->nameInstance,
            'fullName' => $this->formatFullName($avatar),
            'gender' => ($avatar->gender === 'M') ? 'male' : 'female',
            'takePet' => $this->hasTakePet($avatarId),
            'header' => $enHeader,
            'citizenTitle' => 'Commoner',
            'configXML' => Utils::decompressStr($avatar->config),
            'avatarXPs' => $xpData,
            'dateCreated' => $avatar->dateCreated,
            'thumbUrl' => $avatar->thumbUrl,
            'snapUrl' => $avatar->snapUrl,
            'experiment' => $experiment,
            'friends' => $this->getFriends($avatarId),
        ];
        
        // Add pet data if exists
        $petData = $this->getPetData($avatarId);
        if ($petData) {
            $response['pet'] = $petData;
            
            if (pets::where('ownerId', $avatarId)->exists()) {
                $response['ownerId'] = pets::where('ownerId', $avatarId)->first()->ownerId;
            }
        }
        
        return $response;
    }

    /**
     * Generate XML config for client
     * 
     * @param int $userId
     * @param string $sessionId
     * @return string
     */
    private function generateXmlConfig($userId, $sessionId)
    {
        return '<config>
<user>
        <id>'.strval($userId).'</id>
        <justRegistered>N</justRegistered>
        <isNew>N</isNew>
        <isVIP>N</isVIP>
    </user>
    <space>
        <id></id>
        <name></name>
        <modelId></modelId>
        <snapshotFile></snapshotFile>
    </space>
    <webServiceManager>
        <dataService>
            <url>'.GATEWAY_URI.';jsessionid=' . $sessionId.'</url>
            <serviceName>ds</serviceName>
            <timeout>5000</timeout>
            <enableSecureCalls>false</enableSecureCalls>
        </dataService>
        <messageService>
            <timeout>5000</timeout>
        </messageService>
    </webServiceManager>
    <misc>
        <singleBrowserOnly>true</singleBrowserOnly>
        <loadContentFromPackages>false</loadContentFromPackages>
    </misc>
    <paths>
        <webDomain>'.SITE_DOMAIN.'</webDomain>
        <rootPath>'.rtrim(SITE_URI, '/').'</rootPath>
        <consolePath>'.SITE_URI.'profile/</consolePath>
        <storePath>'.SITE_URI.'store/</storePath>
        <newsPath>'.SITE_URI.'news/</storePath>
        <forumPath>'.SITE_URI.'forum/</forumPath>
        <helpPath>'.SITE_URI.'help/</helpPath>
        <supportPath>'.SITE_URI.'support/</supportPath>
        <settingsPath>'.SITE_URI.'settings/</settingsPath>
        <getTokensPath>'.SITE_URI.'store/</getTokensPath>
        <citizenLevelInfoPath>'.SITE_URI.'help/faq/citizen-levels/</citizenLevelInfoPath>
        <skillsInfoPath>'.SITE_URI.'help/faq/levels-xp/</skillsInfoPath>
        <attributesInfoPath>'.SITE_URI.'help/faq/avatar-attributes/</attributesInfoPath>
        <clientPath>'.CONTENT_CONTENT.'main.swf</clientPath>
        <contentPath>'.CONTENT_CONTENT.'</contentPath>
        <packagePath>'.CONTENT_CONTENT.'packages/</packagePath>
        <libraryPath>'.CONTENT_CONTENT.'assets/</libraryPath>
        <configPath>'.SITE_URI2.'config/</configPath>
        <mediaPath>'.MEDIA_URI.'</mediaPath>
        <themePath>'.CONTENT_CONTENT.'themes/</themePath>
        <avatarImagesPath>'.AVATARS_URI.'</avatarImagesPath>
        <spaceImagesPath>'.MEDIA_URI.'images/space/</spaceImagesPath>
        <widgetImagesPath>'.WIDGETS_URI.'</widgetImagesPath>
        <homespacePath>'.SITE_URI.'home/</homespacePath>
        <petTrainingPath>'.SITE_URI.'space/pettraining/</petTrainingPath>
        <plantNurseryPath>'.SITE_URI.'space/gardenlife/</plantNurseryPath>
        <tradingPostPath>'.SITE_URI.'space/tradingpost/</tradingPostPath>
        <buySellForumPath>'.SITE_URI.'forum/forums/66-Buy-amp-Sell</buySellForumPath>
    </paths>
</config>';
    }

    public function userme(Request $request)
    {
        $user = Auth::user();
        $SWSID = $request->header('SWSID');
        
        // Check for header and experiment flags
        $secondaryGroups = explode(',', $user->secondaryGroupIds);
        $enHeader = in_array(18, $secondaryGroups);
        $experiment = in_array(21, $secondaryGroups);
        
        // Set gateway URI based on user groups
        if (in_array(20, $secondaryGroups)) {
            define("GATEWAY_URI", rtrim(SITE_URI, "/") . "/local/swds/gateway");
        } else if (in_array(21, $secondaryGroups)) {
            define("GATEWAY_URI", rtrim(SITE_URI, "/") . "/java/swds/gateway");
        } else {
            define("GATEWAY_URI", rtrim(SITE_URI, "/") . "/swds/gateway");
        }
        
        return \response([
            'id' => strval($user->id),
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'email' => $user->email,
            'goldBalance' => $user->goldBalance,
            'tokensBalance' => $user->tokenBalance,
            'sex' => $user->sex,
            'citizenLevel' => $user->citizenLevel,
            'contentPath' => strval($user->contentPath),
            'citizenTitle' => 'Citizen',
            'citizenImageExt' => 'Citizen',
            'defaultAvatar' => $this->getAvatarResponse($user->defaultAvatar, $enHeader, $experiment),
            'loyalty' => [
                'taskId' => '2',
                'progress' => 3,
                'progressPoints' => 18,
                'addEntry' => true
            ],
            'spaces' => $this->getMySpaces($user),
            'permissions' => $this->getPermissions($user),
            'activeAvatars' => $this->getActiveAvis(),
            'friends' => $this->getFriends($user),
            'webServiceUrl' => GATEWAY_URI .';jsessionid=' . $SWSID,
            'contentUrl' => CONTENT_CONTENT,
            'configUrl' => SITE_URI . "config/",
            'avatarImagesPath' => AVATARS_URI,
            'wwwRoot' => SITE_URI,
            'webassetsPath' => CONTENT_WEBASSETS,
            'config' => $this->generateXmlConfig($user->id, $SWSID),
        ], 200);
    }
    public function experiment(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;
        if ($request->id == Auth::user()->defaultAvatar)
        {
            // add secondary group to 17 on user
            if ($request->enableExperi == true){
                // add 17 to secondary groups
                $secondaryGroups = $request->user()->secondaryGroupIds;
                if ($secondaryGroups == null || $secondaryGroups == "")
                {
                    $secondaryGroups = "21";
                }
                else
                {
                    $secondaryGroups = $secondaryGroups . ",21";
                }
         
                // $secondaryGroups[] = 17;
                $user->secondaryGroupIds = $secondaryGroups;
                $user->save();
            }
            else{
                //remove ONLY 17 from secondary groups 
                $secondaryGroups = $request->user()->secondaryGroupIds;
                //remove 17 from comma separated list
                //if at first position, and has number after comma
                if (strpos($secondaryGroups, "21") == 0 && strpos($secondaryGroups, ",") !== false)
                {
                    $secondaryGroups = str_replace("21,", "", $secondaryGroups);
                }
                else if (strpos($secondaryGroups, "21") == 0 && strpos($secondaryGroups, ",") == false)
                    $secondaryGroups = str_replace("21", "", $secondaryGroups);
                else
                    $secondaryGroups = str_replace(",21", "", $secondaryGroups);

                $user->secondaryGroupIds = $secondaryGroups;
                $user->save();
            }
            return response(['success' => true], 200);
        }
        else
        {
            return response(['success' => false], 200);
        }
    }
    

}

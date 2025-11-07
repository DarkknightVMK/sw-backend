<?php

namespace App\Http\Services;

use App\Models\Avatars;
use App\Models\spaceModels;
use App\Models\avatarSpaces;
use App\Models\sessions;
use App\Models\onlineUsers;
use App\Models\Users;
use App\Models\userGroups;
use App\Models\avatar_xp;

class functions
{
    public $avatar;
    public $spaceId;

    public static function checkSession()
    {
        $session = sessions::where('SWSID', session('id'))->first();

        if ($session->expires_at < now())
        {
            $session->delete();
            onlineUsers::where('avatar_id', session('avatar'))->delete();
            return false;
        }
    
        return true;

    }

    public static function getAvatar($json = null)
    {
        $avatar_json = Avatars::where('avatar_id', session('avatar'))->get();
        $avatar_json = $avatar_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_json, $json2decode);
        $avatar = json_decode($json2decode[0][0], true);
        if ($json == null)
            return $avatar;
        return $avatar[$json];
    }

    public static function getUser($json = null)
    {
        $user_json = Users::where('id', session('user'))->get();
        $user_json = $user_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $user_json, $jsondecode);
        $user = json_decode($jsondecode[0][0], true);
        if ($json == null)
            return $user;
        return $user[$json];
    }

    public static function getUserById($id, $json = null)
    {
        $user_json = Users::where('id', $id)->get();
        $user_json = $user_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $user_json, $jsondecode);
        $user = json_decode($jsondecode[0][0], true);
        if ($json == null)
            return $user;
        return $user[$json];
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function create_guid()
        {
            $charid = md5(uniqid(mt_rand(), true));
            $hyphen = chr(45);// "-"
            $uuid =
                substr($charid, 0, 8)
                . substr($charid, 8, 4)
                . substr($charid, 12, 4)
                . substr($charid, 16, 4);
            return $uuid;
        }

    public static function getAvatarByID($id, $json=null)
    {
        $avatar_json = Avatars::where('avatar_id', $id)->get();
        $avatar_json = $avatar_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_json, $json2decode);
        $avatar = json_decode($json2decode[0][0], true);
        if ($json == null)
            return $avatar;
        return $avatar[$json];
    }

    public function getModelByID($id, $json=null)
    {
        $avatar_json = spaceModels::where('model_id', $id)->get();
        $avatar_json = $avatar_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_json, $json2decode);
        $avatar = json_decode($json2decode[0][0], true);
        if ($json == null)
            return $avatar;
        return $avatar[$json];
    }

    public static function getSpace($id, $json=null)
    {
        $avatar_json = avatarSpaces::where('id', $id)->get();
        $avatar_json = $avatar_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_json, $json2decode);
        $avatar = json_decode($json2decode[0][0], true);
        if ($json == null)
            return $avatar;
        return $avatar[$json];
    }

    public static function getUserPermissions($avi = null)
    {
        if ($avi == null)
            $primaryGroup = functions::getUser('primaryGroupId');
        elseif (strlen($avi) == 32)
            $primaryGroup = functions::getUserById(functions::getAvatarByID($avi, 'owner_id'), 'primaryGroupId');
        else
            $primaryGroup = functions::getUserById($avi, 'primaryGroupId');
        $group = userGroups::where('id', $primaryGroup)->pluck('permissionId');
        $group2 = str_replace('["', '', $group);
        $group3 = str_replace('"]', '', $group2);                 
        return explode(', ', strval($group3));
    }

    public static function reachedCap($xpType, $cap = 4000): bool
    {
        $user = functions::getUser();
        $avatar = functions::getAvatar();
        if (avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->where('xpGiven', '>=', $cap)->count() >= 1 && avatar_xp::where('avatar_id',$avatar['avatar_id'])->where('xpleveltype', $xpType)->count() >= 1 &&// same day
         avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('updated_at', '>=', now()->subDay())->where('xpleveltype', $xpType)->count() >= 1 )
            return true;
        return false;
    }

    public static function giveXP($xpType, $xpAmount)
    {
        $user = functions::getUser();
        $avatar = functions::getAvatar();
        $xp = avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xp;
        switch ($xpAmount)
        {
            case 225120:
                avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->update(['xp' => $xpAmount, 'level' => 499, 'cap' => true, 'updates' => false, 'updated_at' => now()]);
            // update primary level
            avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->update(['xp' =>
            avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', '!=', 8)->sum('xp'),
            'updated_at' => now()]);
            return avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xp;
                break;
            case 158404:
                // crafting
                avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 7)->update(['xp' => $xpAmount, 'level' => 200, 'cap' => true, 'updates' => false, 'updated_at' => now()]);
            // update primary level
            avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->update(['xp' =>
            avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', '!=', 8)->sum('xp'),
            'updated_at' => now()]);
            return avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xp;
                break;
            case 357604:
                //farming
                    avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 6)->update(['xp' => $xpAmount, 'level' => 300, 'cap' => true, 'updates' => false, 'updated_at' => now()]);
            // update primary level
            avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->update(['xp' =>
            avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', '!=', 8)->sum('xp'),
            'updated_at' => now()]);
            return avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xp;
                    break;
        }

        if (avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->first()->level == 499 || avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->first()->level == 311 || avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->level == 499 || avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->level == 700 || avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->level == 800 || avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->level == 900 || avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 6)->first()->level == 300 || avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 7)->first()->level == 200)
            return false;
            
        if (functions::reachedCap($xpType))
        {
            
            // if updated_at is not today, reset xp
            
            
            avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->update(['cap' => true, 'updates' => false, 'updated_at' => now()]);
            
            return false;
        }
        
        // if ($xpAmount == 225120)
        // {
        //     //special max level xp
        //     avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->update(['xp' => 225120, 'level' => 499, 'cap' => true, 'updates' => false, 'updated_at' => now()]);
        //     // update primary level
        //     avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->update(['xp' =>
        //     avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', '!=', 8)->sum('xp'),
        //     'updated_at' => now()]);
        //     return avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xp;
        // }
        if (avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xpGiven + $xpAmount >= 4000)
        {
            // Reached cap logic
            if (avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('updated_at', '>=', now()->subDay())->where('xpleveltype', $xpType)->count() == 0)
            {
                // var_dump('reset');
                avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->update(['xp' => $xp + $xpAmount, 'xpGiven' => $xpAmount, 'cap' => false, 'updates' => true, 'updated_at' => now()]);
                // update primary level xp
                avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->update(['xp' =>
                avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', '!=', 8)->sum('xp'),
                'xpGiven' => avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->first()->xpGiven + $xpAmount,  'updated_at' => now()]);
                return avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xp;
            }
            $xpAmount = 4000 - avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xpGiven;
            avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->update(['xp' => $xp + $xpAmount, 'xpGiven' => 4000, 'cap' => true, 'updates' => false, 'updated_at' => now()]);
            avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->update(['xp' => //sum of all xp paths
            avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', '!=', 8)->sum('xp'),
            'xpGiven' => avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->first()->xpGiven + $xpAmount,  'updated_at' => now()]);
            return avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xp;
        }
        avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->update(['xp' => $xp + $xpAmount, 'xpGiven' => avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xpGiven + $xpAmount,  'updated_at' => now()]);
        // update primary level xp
        avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->update(['xp' =>
        avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', '!=', 8)->sum('xp'),
        'xpGiven' => avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', 8)->first()->xpGiven + $xpAmount,  'updated_at' => now()]);
        return avatar_xp::where('avatar_id', $avatar['avatar_id'])->where('xpleveltype', $xpType)->first()->xp;
    }
    public static function calcXP($level, $levelType)
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
}

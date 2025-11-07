<?php


use App\Models\Avatars;
use App\Models\spaceModels;
use App\Models\Users;
class functions
{
    public $avatar;

    public function getAvatar($json = null)
    {
        $avatar_json = Avatars::where('avatar_id', session('avatar'))->get();
        $avatar_json = $avatar_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_json, $json2decode);
        $avatar = json_decode($json2decode[0][0], true);
        if ($json == null)
            return $avatar;
        return $avatar[$json];
    }
    public function getAvatarByID($id, $json=null)
    {
        $avatar_json = Avatars::where('avatar_id', $id)->get();
        $avatar_json = $avatar_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_json, $json2decode);
        $avatar = json_decode($json2decode[0][0], true);
        if ($json == null)
            return $avatar;
        return $avatar[$json];
    }
    public function getUserByID($id, $json=null)
    {
        $avatar_json = Users::where('id', $id)->get();
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

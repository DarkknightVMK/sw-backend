<?php


use App\Models\Avatars;
use App\Models\spaceModels;
use App\Models\avatarSpaces;

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

    public function getSpace($id, $json=null)
    {
        $avatar_json = avatarSpaces::where('id', $id)->get();
        $avatar_json = $avatar_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_json, $json2decode);
        $avatar = json_decode($json2decode[0][0], true);
        if ($json == null)
            return $avatar;
        return $avatar[$json];
    }
}

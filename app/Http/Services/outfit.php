<?php
include ('functions.php');
use App\Models\Avatars;
use App\Models\Users;
use App\Models\avatar_outfits;
use App\Http\Services\functions;

class outfit
{
    function getOutfits()
    {
        $ret = new stdClass();
        $ret->list = $this->outfits();
        $ret->success = true;
        return $ret;
    }

     function outfits() // fetch all outfits
    {
        $avatar = new functions();
        $outfits = avatar_outfits::where('avatar_id', $avatar->getAvatar('avatar_id'))->get();
        $count = count($outfits);
        $arr = array();
      
      for ($i = 0; $i < $count; $i++)
      {
        $arr[] = array (
            'id' => strval($outfits[$i]['outfit_id']),
            // 'timestamp' => null, // Might be useful?
            // 'userId' => strval($avatar->getAvatar('avatar_id')),
            'data' => $outfits[$i]['outfit_config']
        );
      }
        return $arr;

        // $ret = new stdClass();
        // $ret->id = "8278720";
        // $ret->timestamp = null;
        // $ret->userId = "1";
        // $ret->data = "eNqzyS8tScssUSizVTJUUshLzE21VfJLLVfwBwsrKWSWpOZ6phTbKlmYGZknmxsYJSUnJRubGlkapCSZKunbAQCYXBNf";
        // return $ret;
    }

    function addOutfit($timeconfig, $config)
    {   
        $ret = new stdClass();
        if ($config != null)
        {
            $ret->list = array($this->getNewOutfit($config));
            $ret->success = true;
        }
        else 
            $ret->success = false;
        return $ret;
    }

    function getNewOutfit($config) // create new outfit and return that data
    {
        $avatar = new functions();

        function create_guid()
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
        avatar_outfits::create(
        [
            'outfit_id' => create_guid(),
            'avatar_id' => $avatar->getAvatar('avatar_id'),
            'outfit_config' => $config
        ]);

    }

    function updateOutfit($timconfig, $outfitID, $config)
    {
        $avatar = new functions();

        if (avatar_outfits::where('avatar_id',$avatar->getAvatar('avatar_id'))->where('outfit_id', $outfitID)->get() != null)
        {
            avatar_outfits::where('avatar_id', $avatar->getAvatar('avatar_id'))->where('outfit_id',$outfitID)->update(['outfit_config' => $config]);
            $ret = new stdClass();
            $ret->success = true;
            return $ret;
        } else {

            $ret = new stdClass();
            $ret->success = false;
            return $ret;
        }
        
    }
    function deleteOutfit($timeconfig, $outfitId)
    {
        $avatar = new functions();
        avatar_outfits::where('avatar_id', $avatar->getAvatar('avatar_id') )->where('outfit_id', $outfitId)->delete();
        $ret = new stdClass();
        $ret->success = true;
        return $ret;
    }
}
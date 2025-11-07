<?php
require_once(__DIR__ . '../../../../resources/php/config.php');

use App\Models\avatarArtifacts;
use App\Models\avatarMissionKeys;
use App\Models\Avatars;
use App\Models\avatarSpaces;
use App\Models\spaceModels;
use App\Models\avatarFriends;
use App\Models\avatarBlocks;
use Illuminate\Support\Facades\Storage;
// use Infomaniac\AMF\Deserializer;
use App\result\ErrorCodes;
use App\result\ChoosenAvatarResult;
use App\result\ServiceResult;
use App\result\StringResult;
use App\result\DataResult;
use Carbon\Carbon;
use App\Http\Services\functions;
use App\result\UntypedRecordSetResult;
use App\Models\pets;
use App\Models\onlineUsers;
use App\Models\spaceMember;
use App\Models\spaceBans;
use App\result\UserPermissions;
use App\Models\ai;
use App\Models\avatar_xp;
use App\result\xp\AvatarXP;
use App\Models\sessions;
use Predis\Client;

class avatar
{

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
    function getAvatarOnlineVisible()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }

    function getAvatarStatus($timeconfig, $aid)
    {
        $amf = new stdClass();
        $fr = avatarFriends::where('avatar_id', $aid)->where('friend_id', session('avatar'))->first();
        $rq = avatarFriends::where('avatar_id', session('avatar'))->where('friend_id', $aid)->first();
        $blocked = avatarBlocks::where('avatar_id', session('avatar'))->where('block_id', $aid)->exists();
        if ($fr != null)
        {
            $amf->isFriend = boolval($fr->reciprocal);
            $amf->isBlocked = boolval($blocked);
            $amf->success=true;
        }
        elseif($rq != null)
        {
            $amf->isFriend = boolval($rq->reciprocal);
            $amf->isBlocked = boolval($blocked);
            $amf->success=true;
        }
        else{
            $amf->isFriend = false;
            $amf->isBlocked = boolval($blocked);
            $amf->success=true;
        }
        return $amf;
    }

    function addArtifact($timeconfig, $name, $visible, $ttl)
    {
        $amf = new stdClass();
        $visible = ($visible == 'true') ? 'Y' : 'N';
        // ttl is in seconds convert to timestamp
        avatarArtifacts::create(['avatar_id' => session('avatar'), 'artifact_key' => $name, 'artifact_visible' => $visible, 'artifact_expires' => date('Y-m-d H:i:s', time() + $ttl)]);
        
        $amf->success=true;
        return $amf;
    }

    function removeArtifact($timeconfig, $name)
    {
        $amf = new stdClass();
        if (avatarArtifacts::where('avatar_id', session('avatar'))->where('artifact_key', $name)->exists()){
            avatarArtifacts::where('avatar_id', session('avatar'))->where('artifact_key', $name)->delete();
        }
        else
        {
            $amf->success=false;
        }
        $amf->success=true;
        return $amf;
    }

   function viewMyAvatars() {
      $amf = new stdClass();
      $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
      $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";
      $amf->startIndex = 0;
      $amf->maxLength = 12;
      $amf->totalCount = 10;
      $arr[] = array(

        'block_blocked_avatar_id', 
        'avatar_name_instance',
        'avatar_fname',
        'avatar_type',
        'avatar_id',
        'avatar_desc',
        'avatar_lname',
        'avatar_owner_id',
     );
        $avis = Avatars::where('avatar_id', session('avatar'))->get();
        $pets = pets::where('ownerId', session('avatar'))->get();
        $npc = ai::where('ownerId', session('avatar'))->get();
        foreach ($avis as $av) {
            $arr[] = array(
                
              'null',                
              (float)1,               
              $av->firstName,
              (float)1,
              $av->avatar_id,
                "",
                $av->lastName,
                $av->owner_id,
            );
        }
        foreach ($pets as $pet) {
            $arr[] = array(
              
               $pet->name,
                (float)2,
                $pet->stringId,
                $pet->motto,
                "",
            );
        }
        foreach ($npc as $npc) {
            $arr[] = array(
              'null',
              (float)1,
               $npc->firstName,
                               (float) 3,

                $npc->avatar_id,
                $npc->motto,
                $npc->lastName,
                $npc->ownerId,

            );
        }
      $amf->recordSet = $arr;
      $amf->success= true;
    return $amf;
  }

  function viewAvatars($timeconfig, $aids, $param, $param2) 
  {
    $arr[] = array(
    'block_blocked_avatar_id', 
    'avatar_name_instance',
    'avatar_details',
    'avatar_fname',
    'avatar_head_postfix',
    'avatar_thumb_postfix',
    'avatar_home_space_id',
    'avatar_id',
    'avatar_snapshot_postfix',
    'avatar_online',
    'avatar_online_timestamp',
    'avatar_lname',
    'friend_friend_avatar_id'
    );
    // aids is an array get each
    $avis = Avatars::whereIn('avatar_id', $aids)->get();
    $pets = pets::whereIn('stringId', $aids)->get();
    $npc = ai::whereIn('avatar_id', $aids)->get();
    foreach ($avis as $avi)
    {
        $arr[] = array(
            'null',
             $avi->nameInstance,
            null, // todo, motto
            $avi->firstName,
            $avi->headPostfix,
            $avi->thumbPostfix,
            $avi->homeSpaceId,
            $avi->avatar_id,
            $avi->snapshotPostfix,
            onlineUsers::where('avatar_id', $avi->avatar_id)->where('online', true)->exists(),
            (onlineUsers::where('avatar_id', $avi->avatar_id)->where('online', true)->exists()) ? Carbon::createFromTimestamp(strtotime(onlineUsers::where('avatar_id', $avi->avatar_id)->where('online', true)->first()->updated_at))->format("Y-m-d H:i:s") : null,
            $avi->lastName,
            null
        );
    }
    foreach ($npc as $npc) {
        $arr[] = array(
          'null', // block_blocked_avatar_id
          '1', // avatar_name_instance
          $npc->motto,
          $npc->firstName,
           $npc->headpostfix, // avatar_head_postfix
              $npc->thumbpostfix, // avatar_thumb_postfix
                'null',      // avatar_home_space_id
      
                 $npc->avatar_id,
                    $npc->snapshotpostfix, // avatar_snapshot_postfix
                    onlineUsers::where('avatar_id', $npc->avatar_id)->where('online', true)->exists(),
                    (onlineUsers::where('avatar_id', $npc->avatar_id)->where('online', true)->exists()) ? Carbon::createFromTimestamp(strtotime(onlineUsers::where('avatar_id', $npc->avatar_id)->where('online', true)->first()->updated_at))->format("Y-m-d H:i:s") : null,

            $npc->lastName,
            'null', // friend_friend_avatar_id
        );
    }
    // var_dump($arr);
    return new UntypedRecordSetResult($arr);
    
  }
  
  function getCurrentAvatar() {
    $amf = new stdClass();
    $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
    $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";
    $amf->startIndex = Null;
    $amf->maxLength = Null;
    $amf->totalCount = 1;
    $param =null;
    $param2 = session('avatar');
    $amf->recordSet = array($this->chooseAvatar($param,$param2));
    $amf->success= true;
  return $amf;
}
     function checkAvatarNameInstances($p, $p2, $p3)
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.entity.avatar.external.amf.result.ChoosenAvatarResult";
        $amf->success = true;
        return $amf;
    }

   function chooseAvatar($timeconfig, $aid)
  {
    // $redis = new Client();
    // is in redis?
    // $chosen_avatar = $redis->get("chosen_avatar:$aid");
        if (Avatars::where('avatar_id', $aid)->exists())
        {
            // $redis->set("chosen_avatar:$aid", $aid);
      $amf = new stdClass();
      $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
      $amf->$explicitTypeField = "com.smallworlds.entity.avatar.external.amf.result.ChoosenAvatarResult";
      $amf->thumbPostfix = $this->getAvatar('headPostfix');
      $amf->avatarPremiumOptions = "Y";
      $amf->snapshotPostfix = $this->getAvatar('snapshotPostfix');
      $amf->avatarId = $aid;//$this->create_guid();
      
      $amf->avatarNameInstance = $this->getAvatar('nameInstance');
      $amf->avatarFName = $this->getAvatar('firstName');
      $amf->avatarLName = $this->getAvatar('lastName');
      $amf->avatarUniqueSpaceVisits = 0;

      $artifacts = avatarArtifacts::where('avatar_id', $this->getAvatar('avatar_id'))->get();
      $time = null;
      $prevKey = null;
      if (!$artifacts->isEmpty())
      {
        foreach ($artifacts as $artifact)
        {
            //if two artifacts have the same name
                if ($prevKey == $artifact->artifact_key)
                {
                    // compare the two artifacts expiry dates
                    if ($time)
                    {
                        if (strtotime($artifact->artifact_expires) > $time)
                        {
                            $time = date('Y-m-d H:i:s',  $time);
                            avatarArtifacts::where('avatar_id', $aid)->where('artifact_expires', $time)->delete();
                        }
                    }
                $time = strtotime($artifact->artifact_expires);
                $prevKey = $artifact->artifact_key;
                }
                //convert to seconds
                $artifact->artifact_expires = strtotime($artifact->artifact_expires);
                
            $amf->artifacts[] = $artifact->toArray();
        }
      }
      else
        $amf->artifacts = array();
      $amf->avatarName = ($this->getAvatar('nameInstance') > 1) ? strtolower($this->getAvatar('fullName')).$this->getAvatar('nameInstance') : strtolower($this->getAvatar('fullName'));
      $amf->homeSpaceId = strval($this->getAvatar('homeSpaceId'));
      $amf->timeInSpaces = 22000;
      $amf->config = $this->getAvatar('config');
      // get the avatar's missionKeys
      $missionKey = avatarMissionKeys::where('avatar_id', $aid)->get();
      if (!$missionKey->isEmpty())
      {
        foreach ($missionKey as $key)
        {
            $amf->missionkey_expires = strtotime($key->missionkey_expires);
            $amf->missionKeys[] = $key->toArray();
        }
      }
      else
        $amf->missionKeys = array();
      $amf->success=true;
      $amf->headPostfix = $this->getAvatar('headPostfix');
    
      return $amf;
    }
    else return new ServiceResult(false, ErrorCodes::AVATAR_NOT_FOUND);


  }

   function getAvatarHomeSpaceInfo($timeconfig, $fullname)
  {

      $homeSpaceID = Avatars::where('fullName', 'LIKE', '%'. $fullname. '%')->pluck('homeSpaceId')->first();
    //   var_dump($homeSpaceID);
      // find any number in the fullname
      if ($homeSpaceID == null && $fullname != null)
      {
        $number = preg_replace('/[^0-9]/', '', $fullname);

        //strip the number from the fullname
        $fullname = str_replace($number, '', $fullname);

        // var_dump($number);
        $nameInstance = Avatars::where('fullName', 'LIKE', '%'. $fullname. '%')->where('nameInstance', $number)->pluck('homeSpaceId')->first();
        $avatarSpace = avatarSpaces::where('id', $nameInstance)->get()[0];
        $modelId =  $avatarSpace['modelId'];
        $model = spaceModels::where('model_id', $modelId)->get();
        $amf = new stdClass();
        $amf->id = $nameInstance;
        $amf->modelSource = $model[0]['model_source'];
        $amf->config = $avatarSpace["config"];
      //   $amf->model = $model[0];
        $amf->success = true;
      }
      elseif ($homeSpaceID != null) {
      $avatarSpace = avatarSpaces::where('id', $homeSpaceID)->get()[0];
      $modelId =  $avatarSpace['modelId'];
      $model = spaceModels::where('model_id', $modelId)->get();
      $amf = new stdClass();
      $amf->id = $homeSpaceID;
      $amf->modelSource = $model[0]['model_source'];
      $amf->config = $avatarSpace["config"];
    //   $amf->model = $model[0];
      $amf->success = true;
      }
        else {
            $amf = new stdClass();
            $amf->success = false;
        }           
      return $amf;
  }



  function choosePet($timeconfig, $petId = null)
  {

        if($petId == null)
            return new ServiceResult(true);
        else
            return new ChoosenAvatarResult($petId, true);
    
  }

   function getInWorldCountForLogin()
  {
      $amf = new stdClass();
      $amf->data = 0;
      $amf->success = true;
      return $amf;
  }
    function viewMyBlocks()
    {
        if (avatarBlocks::where('avatar_id', session('avatar'))->exists())
        {
            $arr[] = array('block_blocked_avatar_id');
            foreach (avatarBlocks::where('avatar_id', session('avatar'))->get() as $block)
            {
                $arr[] = array($block->block_id);
            }
        }
        else
            $arr = array();
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";
        $amf->startIndex = 0;
        $amf->maxLength = 0;
        $amf->totalCount = 0;
        $amf->recordSet = $arr;
        $amf->success=true;
        // $amf->command = null;
        return $amf;


    }
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

    function saveAvatarSnapshot($timeconfig, $avatar_id, $snapshot)
    {
        $avatar_url = \Config::get('custom.avatars_url');
        $postfix = Avatars::where('avatar_id', $avatar_id)->pluck('snapshotPostfix')->first();

        if (Storage::disk('avatars')->exists($avatar_id . $postfix . "_snap.png") === true)
        {
            Storage::disk('avatars')->delete($avatar_id . $postfix . "_snap.png");
        }
        $filename = $this->generateRandomString();
        Avatars::where('avatar_id', $avatar_id)->update(['snapshotPostfix' => $filename,  'snapUrl' => $avatar_url .'/' . $avatar_id. $filename. '_snap.png']);
        $this->uploadFile($snapshot, $avatar_id . $filename . "_snap.png");
        $amf = new stdClass();
        $amf->success = true;
        return $amf;
    }


     function saveAvatar($timeConfig,$avatar_id,$avatar_fName,$avatar_lName,$unknown,$config,$head, $snap, $thumb,$param)
    {
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
//        if (Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first() == 'epM1RMX9DC' || Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first() == 'BvqP7b1Tfi') {
            $filename = $this->generateRandomString();
            //$mediaURI = env('MEDIA_URL')
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
//        if (Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first() == 'epM1RMX9DC' || Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first() == 'BvqP7b1Tfi') {
            $filename = $this->generateRandomString();
            //$mediaURI = env('MEDIA_URL')
            ai::where('avatar_id', $avatar_id)->update(['headPostfix' => $filename, 'snapshotPostfix' => $filename, 'thumbUrl' => $avatar_url .'/'. $avatar_id. $filename. '_thumb.png', 'snapshotUrl' => $avatar_url .'/' . $avatar_id. $filename. '_snap.png']);
            $this->uploadFile($head, $avatar_id . $filename . ".png");
            $this->uploadFile($thumb, $avatar_id . $filename . "_thumb.png");
            $this->uploadFile($snap, $avatar_id . $filename . "_snap.png");

            ai::where('avatar_id', $avatar_id)->update(['configString' => $config]);
        }

//        }
//        else
//            $filename = Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first();

        

        $amf = new stdClass();
        $amf->success = true;
        return $amf;

    }
    function updateAvatarConfig($timeconfig, $avatar_id, $config)
    {
        if (Avatars::where('avatar_id',$avatar_id)->exists())
        {
            Avatars::where('avatar_id', $avatar_id)->update(['config' => $config]);
        }
        else if (Ai::where('avatar_id',$avatar_id)->exists())
        {
            Ai::where('avatar_id', $avatar_id)->update(['configString' => $config]);
        }
        // Avatars::where('avatar_id', $avatar_id)->update(['config' => $config]);
        $amf = new stdClass();
        $amf->success = true;
        return $amf;
    }
    function uploadFile($file, $name)
    {

       $str = amf_encode($file->data, AMF_CLASS_MAPPING);

       Storage::disk('avatars')->put( $name,  amf_decode($str));

    }
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    function getMyPets()
    {
        $amf = new stdClass();
        $arr[] = array ('petOwnerId',
    'id'
    );
        // $arr[] = array (session('avatar'));
        $arr[] = array (strval(session('avatar')),
        strval(session('avatar')),
        
    );
$pet = pets::where('ownerId', session('avatar'))->get();
$petArr = array();
if (pets::where('ownerId', session('avatar'))->exists())
{

    foreach ($pet as $p)
    {
        // $petArr = array('avatarMem' => $p->memoryString);
        $petArr[] = array(
          
            'pet' => array(
            'petOwnerId' => strval($p->ownerId),
            'id' => strval($p->stringId),
            'snapshotPostfix' => $p->snapshotPostfix,
            'thumbPostfix' => $p->thumbPostfix,
            'headPostfix' => $p->headPostfix,
            'fName' => $p->name,
            ),
        
            // do calculation of created_at vs current time in seconds

            // 'lifespanSeconds' => $p->created_at - time(),
        );
    }
    
}
        $amf->pets= 
        $petArr;

        // $amf->currentOwnerId = strval(session('avatar'));
        $amf->success=true;
        return $amf;
    }

    function getMyAvatarXPLevels($timeconfig, $aid)
    {
        // $amf = new stdClass();
        return new DataResult(
            //return array of xp levels of each skill
            json_decode( avatar_xp::where('avatar_id', $aid)->get()->map(function ($xp) {
                return new AvatarXP(
                    $xp
                );
            }))
        );
        // $amf->data = array(
        // 'avatarId' => $aid,
        // 'xpType' 
        // 'xp'
        // 'level'

        // );
        // $amf->success = true;

        // return $amf;
    }

    function addFriend($timeconfig, $friend_id)
    {
          //TODO add friend
          if (avatarFriends::where('avatar_id', session('avatar'))->where('friend_id', $friend_id)->exists())
          {
              return new ServiceResult(false);
          }
          else
          {
              $isOnline = onlineUsers::where('avatar_id', $friend_id)->where('online',true)->exists();
              if ($isOnline)
              {
                $userId = Avatars::where('avatar_id', $friend_id)->pluck('owner_id')->first();
                $sessionId = sessions::where('user_id', $userId)->pluck('SWSID')->first();
                // $server = new SabreAMF_Client("https://".SITE_DOMAIN."/swds/gateway;jsessionid=".$sessionId);
                // $server->sendRequest('friend.findMyFriends', array());
                
              }
              
              $friend = new avatarFriends;
              $friend->avatar_id = session('avatar');
              $friend->friend_id = $friend_id;
              $friend->save();
              return new ServiceResult();
          }
          
          return new ServiceResult(false);
    }

    function removeFriend($timeconfig, $friend_id)
    {
        //TODO remove friend
        if (avatarFriends::where('avatar_id', session('avatar'))->where('friend_id', $friend_id)->exists())
        {
            avatarFriends::where('avatar_id', session('avatar'))->where('friend_id', $friend_id)->delete();
            return new ServiceResult();
        }
        else
        {
            return new ServiceResult(false);
        }
    }

    function addBlock($timeconfig, $blockId)
    {
        //TODO add block
        if (avatarBlocks::where('avatar_id', session('avatar'))->where('block_id', $blockId)->exists())
        {
            return new ServiceResult(false);
        }
        else
        {
            $block = new avatarBlocks;
            $block->avatar_id = session('avatar');
            $block->block_id = $blockId;
            $block->save();
            return new ServiceResult();
        }
    }
    function removeBlock($timeconfig, $blockId)
    {
        //TODO remove block
        if (avatarBlocks::where('avatar_id', session('avatar'))->where('block_id', $blockId)->exists())
        {
            avatarBlocks::where('avatar_id', session('avatar'))->where('block_id', $blockId)->delete();
            return new ServiceResult();
        }
        else
        {
            return new ServiceResult(false);
        }
    }

    function createPet($timeconfig, $petName, $motto, $unk2, $config, $headImage, $snapshotImage, $thumbImage, $ownerId, $bool)
    {
        $amf = new stdClass();

        $avatar_url = \Config::get('custom.avatars_url');
            // $postfix = pets::where('ownerId', $ownerId)->pluck('headPostfix')->first();

            // if (Storage::disk('avatars')->exists($avatar_id . $postfix . ".png") === true)
            // {
            //     Storage::disk('avatars')->delete($avatar_id . $postfix . ".png");
            //     Storage::disk('avatars')->delete($avatar_id . $postfix . "_thumb.png");
            //     Storage::disk('avatars')->delete($avatar_id . $postfix . "_snap.png");
            // }
            $filename = $this->generateRandomString();

            $pet = pets::create(
                [
                    'stringId' => functions::generateRandomString(32),
                    'name' => $petName,
                    'configString' => $config,
                    'memoryString' => 'eNptj0sOwjAMRK9ieQ/9l0RK2h0ngAOEEJGIxqmaCMHtCT/Bgt2M5Tdji/HqJ7iYJbpAEqt1iWBIh6Ojk8T9brtiCDEpOqopkJFIAcdBeOMHoQMlpVMcRPGVOnift1/Dj0yL0+cIuaDBYhA5b4kwR4nlw1qjpmTB5vSWI6R8Rst7xjfN06o8r2rW9FkfJokdq1j75NQ8OzIxglUS+/o/ylve8Tf6qrudnCED9iZx0/2H6rIvWf1LFY+X76AvV/I=',
                    'ownerId' => $ownerId,
                    'headPostfix' => $filename, 'snapshotPostfix' => $filename, 'thumbPostfix' => $filename,'thumbUrl' => $avatar_url .'/'.  $filename. '_thumb.png', 'snapshotUrl' => $avatar_url .'/' . $filename. '_snap.png', 'headUrl' => $avatar_url .'/' .  $filename. '.png',
                
                ]
                );
//        if (Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first() == 'epM1RMX9DC' || Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first() == 'BvqP7b1Tfi') {
            //$mediaURI = env('MEDIA_URL')
            // Avatars::where('avatar_id', $avatar_id)->update(['headPostfix' => $filename, 'snapshotPostfix' => $filename, 'thumbPostfix' => $filename,'thumbUrl' => $avatar_url .'/'. $avatar_id. $filename. '_thumb.png', 'snapshotUrl' => $avatar_url .'/' . $avatar_id. $filename. '_snap.png', 'headUrl' => $avatar_url .'/' . $avatar_id. $filename. '.png']);
//        }
//        else
//            $filename = Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first();

        $this->uploadFile($headImage, $pet->stringId . $filename . ".png");
        $this->uploadFile($thumbImage, $pet->stringId . $filename . "_thumb.png");
        $this->uploadFile($snapshotImage, $pet->stringId . $filename . "_snap.png");

        // Avatars::where('avatar_id', $avatar_id)->update(['config' => $config]);

        $amf->data = $pet->stringId;
        $amf->success = true;
        return $amf;
    }

    function savePet($timeconfig, $petId, $petName, $motto, $unk2, $config, $headImage, $snapshotImage, $thumbImage, $ownerId, $bool)
    {
        // if ($ownerId != session('avatar'))
        //     return new ServiceResult(false);
        $amf = new stdClass();

        $avatar_url = \Config::get('custom.avatars_url');
            // $postfix = pets::where('ownerId', $ownerId)->pluck('headPostfix')->first();

            // if (Storage::disk('avatars')->exists($avatar_id . $postfix . ".png") === true)
            // {
            //     Storage::disk('avatars')->delete($avatar_id . $postfix . ".png");
            //     Storage::disk('avatars')->delete($avatar_id . $postfix . "_thumb.png");
            //     Storage::disk('avatars')->delete($avatar_id . $postfix . "_snap.png");
            // }
            $filename = $this->generateRandomString();
            if (!pets::where('stringId', $petId)->exists())
            {
                //it's an ai
                ai::where('avatar_id', $petId)->update(
                    [
                        // 'name' => $petName,
                        'motto' => $motto,
                        'configString' => $config,
                        // 'ownerId' => $ownerId,
                        'headPostfix' => $filename, 'snapshotPostfix' => $filename, 'thumbPostfix' => $filename,'thumbUrl' => $avatar_url .'/'. $petId. $filename. '_thumb.png', 'snapshotUrl' => $avatar_url .'/' . $petId. $filename. '_snap.png', 'headUrl' => $avatar_url .'/' . $petId. $filename. '.png',
                    
                    ]
                    );
                $pet = ai::where('avatar_id', $petId)->first();
                $this->uploadFile($headImage, $pet->avatar_id . $filename . ".png");
                $this->uploadFile($thumbImage, $pet->avatar_id . $filename . "_thumb.png");
                $this->uploadFile($snapshotImage, $pet->avatar_id . $filename . "_snap.png");

                $amf->data = $pet->avatar_id;
            }
            else 
            {
             pets::where('stringId', $petId)->where('ownerId', $ownerId)->update(
                [
                    'name' => $petName,
                    'motto' => $motto,
                    'configString' => $config,
                    // 'ownerId' => $ownerId,
                    'headPostfix' =>  $filename, 'snapshotPostfix' => $filename, 'thumbPostfix' =>  $filename,'thumbUrl' => $avatar_url .'/'. $petId. $filename. '_thumb.png', 'snapshotUrl' => $avatar_url .'/' . $petId . $filename. '_snap.png', 'headUrl' => $avatar_url .'/' . $petId . $filename. '.png',
                
                ]
                );
                $pet = pets::where('stringId', $petId)->where('ownerId', $ownerId)->first();
//        if (Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first() == 'epM1RMX9DC' || Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first() == 'BvqP7b1Tfi') {
            //$mediaURI = env('MEDIA_URL')
            // Avatars::where('avatar_id', $avatar_id)->update(['headPostfix' => $filename, 'snapshotPostfix' => $filename, 'thumbPostfix' => $filename,'thumbUrl' => $avatar_url .'/'. $avatar_id. $filename. '_thumb.png', 'snapshotUrl' => $avatar_url .'/' . $avatar_id. $filename. '_snap.png', 'headUrl' => $avatar_url .'/' . $avatar_id. $filename. '.png']);
//        }
//        else
//            $filename = Avatars::where('avatar_id',$avatar_id)->pluck('headPostfix')->first();

            $this->uploadFile($headImage, $pet->stringId . $filename . ".png");
            $this->uploadFile($thumbImage, $pet->stringId . $filename . "_thumb.png");
            $this->uploadFile($snapshotImage, $pet->stringId . $filename . "_snap.png");

            // Avatars::where('avatar_id', $avatar_id)->update(['config' => $config]);

            $amf->data = $pet->stringId;
        }

        $amf->success = true;
        return $amf;
    }


    function getPetMem($timeconfig, $petId)
    {
        if (!pets::where('stringId', $petId)->exists())
            return new StringResult(ai::where('avatar_id', $petId)->first()->memoryString);
        else
            return new StringResult(pets::where('stringId', $petId)->first()->memoryString);
    }

    function savePetMem($timeconfig, $petId, $mem)
    {
        if (!pets::where('stringId', $petId)->exists())
            ai::where('avatar_id', $petId)->update(['memoryString' => $mem]);
        else
            pets::where('stringId', $petId)->update(['memoryString' => $mem]);
        return new ServiceResult();
    }


    function getAvatarRoleId($timeconfig, $spaceId, $aid)
    {
        if (spaceMember::where('space_id', $spaceId)->where('avatar_id', $aid)->pluck('spacerole_id')->first() == null)
            return new StringResult("");
        else
            return new StringResult((float)spaceMember::where('space_id', $spaceId)->where('avatar_id', $aid)->pluck('spacerole_id')->first());
        // return new StringResult(null);

    }

    function banAvatarFromSpace($timeconfig, $avId, $spaceId, $reason)
    {
        if (in_array(UserPermissions::GUI_STAFF, functions::getUserPermissions($avId)) || in_array(UserPermissions::SPACE_ADMIN, functions::getUserPermissions($avId)))
            return new ServiceResult(false, ErrorCodes::DO_NOT_HAVE_PERMISSION);
    

        spaceBans::create(
            [
                'space_id' => $spaceId,
                'avatar_id' => $avId,
                'reason' => $reason,
                'expires' => null
            ]
        );
        return new ServiceResult();
    }

    function removeAvatarSpaceBan($timeconfig, $id)
    {
        // var_dump(session());
        spaceBans::where('id', $id)->delete();
        return new ServiceResult();
    }

 
}

<?php
use App\Models\Avatars;
use App\Models\avatarFriends;
use App\result\AvatarIdentity;
use App\result\FriendRequest;

class friend
{
    function findMyFriends()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.ListResult";

        $avatar = avatarFriends::where('avatar_id', session('avatar'))->get();
        // var_dump($avatar);
        
        if ($avatar->isEmpty())
        {
           $avatar = avatarFriends::where('friend_id', session('avatar'))->get();

        //    var_dump($avatar);
        foreach ($avatar as $friend)
        {
            $fr = Avatars::find($friend->avatar_id);
            // $arr[] = array(
            //     'request' => (!$friend->reciprocal) ? new FriendRequest($fr) : null,
            //         'avatar' => new AvatarIdentity($fr),
            //         'user' => null,
            // );
        
        // $amf->list = $arr;
        
            if (!$friend->reciprocal)
            {
                $amf->list = array(array(
                    'request' => new FriendRequest($fr),
                    'avatar' => new AvatarIdentity($fr),
                    'user' => null,
                ));
            }
            else {
            $amf->list = array(array(
                'request' => null,
                'avatar' => new AvatarIdentity($fr),
                'user' => null,
            )); }
        }
        }
        // is collection empty? 

        elseif (!$avatar->isEmpty() )
{  
                // var_dump($avatar);

    $avatar = avatarFriends::where('friend_id', session('avatar'))->get();

    //    var_dump($avatar);
    foreach ($avatar as $friend)
    {
        $fr = Avatars::find($friend->avatar_id);
        $arr[] = array(
            'request' => (!$friend->reciprocal) ? new FriendRequest($fr) : null,
                'avatar' => new AvatarIdentity($fr),
                'user' => null,
        );
    
    // $amf->list = $arr;
    
        // if (!$friend->reciprocal)
        // {
        //     $amf->list = array(array(
        //         'request' => new FriendRequest($fr),
        //         'avatar' => new AvatarIdentity($fr),
        //         'user' => null,
        //     ));
        // }
        // else {
        // $amf->list = array(array(
        //     'request' => null,
        //     'avatar' => new AvatarIdentity($fr),
        //     'user' => null,
        // )); }
    }
    $avatar = avatarFriends::where('avatar_id', session('avatar'))->get();


            foreach ($avatar as $friend)
            {
                $fr = ($friend != null) ? Avatars::find($friend->friend_id) : null;
                             $rq = avatarFriends::where('friend_id', session('avatar'))->get();

                $arr[] = array(
                    // 'request' => ($rq != null && !$rq->reciprocal) ? new FriendRequest($fr) : null,
                    'avatar' => new AvatarIdentity($fr),
                    'user' => null,
                );
            }
            $amf->list = $arr;

            // $amf->list = array(array(
            //     'avatar' => new AvatarIdentity($friend),
            //     'user' => null,
            // ));
        }
        else
        {
            $amf->list = array();
        }
        $amf->success=true;
        return $amf;
    }
    function getAvatarFriendCount($timeconfig, $uid)
    {
        $amf = new stdClass();
        // $amf->list = array('value'=>0);
        $fr = avatarFriends::where('avatar_id', $uid)->get();
        $unreciprocal = 0;
        $reciprocal = 0;

        foreach ($fr as $friend)
        {
            if (!$friend->reciprocal)
            {
                $unreciprocal++;
            }
            else
            {
                $reciprocal++;
            }
        }
        $amf->unreciprocalFriendCount = $unreciprocal; // PENDING - get from DB
        $amf->reciprocalFriendCount = $reciprocal; // TODO Friend count
        $amf->success=true;
        return $amf;
    }
    
    function searchForFriends($timeconfig, $q, $active)
    {
        $amf = new stdClass();
        // var_dump($q);
        $q = trim($q);
        $avatar = Avatars::where('fullName', 'LIKE', '%'.$q.'%')->get();

        if ($avatar->first() == null)
        {
            $amf->list = array();
            $amf->success=true;
        return $amf;
        }
        // foreach ($avatar as $friend)
        // {
        //     $arr[] = array(
        //         'request' => null,
        //         'avatar' => new AvatarIdentity($friend),
        //         'user' => null,
        //     );
        // }
    //     {
          
        for($i=0; $i<count($avatar); $i++)
        {
           $arr[] = array 
           (
               'request' => null,

                'avatar' => array
                (
                    'lastName' => $avatar[$i]->lastName,
                    'nameInstance' => $avatar[$i]->nameInstance,
                    'isReciprocal' => false,
                    'currentInstanceId' => null,
                    'online' => false,
                    'id' => $avatar[$i]->avatar_id,
                    'isFriend' => false,
                    'homeSpaceId' => $avatar[$i]->homeSpaceId,
                    'currentSpaceId' => null,
                    'onlineTimestamp' => null,
                    'primaryXP' =>'999',
                    'bio' => 'hi',
                    'userId' => $avatar[$i]->avatar_id,
                    'snapShotUrl' => $avatar[$i]->snapUrl,
                    'primaryXPLevel' => '999',
                    'thumbUrl' => $avatar[$i]->thumbUrl,
                    'headUrl' => $avatar[$i]->thumbUrl,
                    'firstName' => $avatar[$i]->firstName,

                ),
                'user' => null,
           );
        }
        $amf->list = $arr;
    // }
    // else
    //     {
    //         $amf->list = array();
    //     }
        $amf->success=true;
        return $amf;
    }
}

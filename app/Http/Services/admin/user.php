<?php

use App\Models\Users;
use App\Models\Avatars;
use App\Models\avatarItems;
use App\Models\spaceModels;
use App\Models\avatarSpaces; 
use App\Models\onlineUsers;   
use App\Models\userGroups;    
use RTMPClient as RtmpClient;

class user
{
    function bootUser($timeconfig, $userId)
    {
        require "../vendor/qwantix/php-rtmp-client/RtmpClient.class.php";
        require "../vendor/qwantix/php-rtmp-client/debug.php";

        $client = new RtmpClient();
        $connectParams = array(
            '109' );
        $client->connect("192.168.1.168","swms", 1935, $connectParams);
        $result = $client->call("bootUser", array($timeconfig, $userId));
        var_dump($result);
    }
    function getAbuseReportManagers()
    {
        $amf = new stdClass();
//        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
//        $amf->$explicitTypeField = "com.smallworlds.entity.space.result.LookupSpaceResult";
        $amf->data = "eNpztDXQUXAGEQEgwh1EuIKIUBDhCyKCbQ0Al7gH0g==";
        $amf->success=true;
        return $amf;
    }
    function getEscalators()
    {
        $amf = new stdClass();
//        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
//        $amf->$explicitTypeField = "com.smallworlds.entity.space.result.LookupSpaceResult";
        $amf->success=true;
        return $amf;
    }

    function getInfractors()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
    function searchUsers($timeconfig, $userID, $param2, $param3, $param4, $avatarID, $fName, $lName, $nameI, $param5, $exact, $online)

    {
        $amf = new stdClass();
        $amf->recordSet = $this->searchU($userID, $fName, $lName);
//        $amf->
//        $amf->
//        $amf->

        $amf->success=true;
        return $amf;
    }

    function saveUserDetails($timeconfig, $id, $fName, $lName, $email, $password, $gender, $unk, $securityQ, $securityAns)
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }

    function createUser($timeconfig, $name, $lname, $email, $password, $dobD, $dobM, $dobY, $secret, $avatarFName, $avatarLName, $gender )
    {
        $amf = new stdClass();
        $amf->success = false;
        return $amf;
        $avatar_url = \Config::get('custom.avatars_url');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $amf->success=false;
            $amf->code=100;
            return $amf;
        }


        $request = \Validator::make
        (
            [
                'avatarFName' =>  $avatarFName,
                'avatarLName' => $avatarLName,
                'avatarGender' => $gender,
                'userFName' => $name,
                'userLName' => $lname,
                'email' => $email,
                'password' => $password,
                'dobMonth' => strval($dobM),
                'dobDate' => strval($dobD),
                'dobYear' => strval($dobY),
                'sex' => $gender,
                'nameInstance' => '1',
            ],
            [
                'avatarFName' => 'required|string',
                'avatarGender' => 'required|string',
                'avatarLName' => 'required|string',
                'nameInstance' => 'required|string',
                'userFName' => 'required|string',
                'userLName' => 'required|string',
                'sex' => 'required|string',
                'dobMonth' => 'required|string',
                'dobDate' => 'required|string',
                'dobYear' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|string',

            ]);
        if (!$request->fails())
        {

            // var_dump($request);
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




        function createItems($item_id, $ic, $model_id, $aid)
        {
         avatarItems::create(
             [
                 'item_id' => $item_id,
                 'model_id' => $model_id,
                 'item_count' => $ic,
                 'avatar_id' => $aid,
                 'item_config' => '',
             ]
         );
        }
        $user = Users::create
        (
            [
                'firstName' => $name,
                'lastName' => $lname,
                'sex' => $gender,
                'dob_month' => strval($dobM),
                'dob_day' => strval($dobD),
                'dob_year' =>  strval($dobY),
                'email' => $email,
                'password' => bcrypt($password),
                'goldBalance' => 5000000,
                'tokenBalance' => 1000000,
                'citizenLevel' => 1,
            ]
        );


        $womenConfig = "eNrlV0tv4jAQ/itRzi0kJKEglUpNuxRVlFYNUrVHiwxNhImR7Szl3+84j5bYTg897p5w5vtmxvNMuCZ/iCTceYciBT5zt7AnFNyba3aQOSuEc0Dh6vPZydOZe1dK+HWCJCMpOz7XPNcRQGtomb9nMubsWNQMd9jRjpn0/IZugUb9UNAPRXZozQ6e1w/5/dA3Bkf9UNAPhT/yNe6HrvqhST80tUN3rEjKXNpBhYhek3eMMt7tggcOJ434jI3BLdSYks3OLKhkewv5CdKYlqDTgUoL+S3LpU5dwWaHDsFC/w2UGr36UE1FlzivJ6RLfEwwEw3hsdwf1iLLuTRIdWRnvBdSYGp12hygLcWKJRkzImbpKdnlRcO55XtxsYR3cbFmXDCN/PNJXeYHIfPNrquI0kRJXwjd61WeY25jLV0q3ehrfWQWcos4x5nrDSJPozyxUmaGe9FewcZec1KILeOYZXmauZf+wPfHkRdOojAYT6YBTi9utEtv4HnhlT+eTnw/GHlXUVBN6Ie6xjQMgul4NBqHfuRNQyU/WeVmPLf24J8L6AleIT3BL7A6VFWoa3NBcr7OCxlTIJtMb3Am9DsoUSI5qSwZ99DRnrsoWmzajUvs58IsbRerbXq6zQdKhADRtarnAIMV8kRBywF/ZDSHzwxBqundw4bQby3PWVmkRD12aUkGUKyYkShcPCLTd8ESV0wD2OnWjVchT4SfKI6wMaxdNgowQ1C8AqH6eMJWInzW8Kp9/YEXBKOo7tn2oav4qlL2I021K7RoUCJwL+fl3gzFEn0rrVePucwXxLbLW6ntbaFaxKrSiGPKcInr7y0cIPtY3ROue1Cr1uLhU7wmVRmHzSfTzXVKig20v3U/Vicfv6O2Tqaajx8JT12HYDWAkg9sYIeenXFF4XcLKlYVqHQWhG5VySulN0J3QaWiTn6lEEYWjarWhp9GvcfLl05jvNEIK43AP/MzrEI0Ix39N5EG/2ykw7aNh/V/hJu/ROHnYw==";
        $menConfig = "eNqdVt1v2jAQf99fEeUZjZDA1kpQCegYquiHCNXUR5ccxMKxke2U8d/PTgJtgh23e0t+H3dn53zxEL0hibi3BZoAH/kZIuDffPO8IdtLzKjw9gp9KaAz6OFk5E9zCb+OEKcoYYfHUux7AkhJLfA2lRPODrRU+N3LEBMmg6Dy2Piegw8dfOTgBy38iu1b69N8z8GHDj5y8H0H76r/h4P/6eCvHPx1Cz9lNM6xbFFoWrRnmDLCeL2//qRYgkn+qBqPGwxFH1o6QLLM5CBovTM6gMivlPQA652KBQbPCxBiPhi/i9NYV9/rk2nQ3sVqkyrNXZ7tVyLFXJqV5XI/iJ8QVftv1M4ATl/ugcUpA/P+Jcd4h2klHPNMdBawFZ0V44IZl0aQECDqa6vA8RtGknGTbaa2cFw3FRDFGTxSsFpOvHcY+cH3QWATTi5jL1lOk9WB2Sxnvi32AxPNujU0I0hayv5IuyJPLiPHkiM9ei2FNyVtGdR0r8dXgIglogniyRIQMZkWsJFKt+KIig3jmUmz1NldojnCXMgjadSg4fGGGxd3C2tE6vICugWBtxR4LPPXVwKdJwx8jek2NAW5Z7lMG0dPQ3GGdS1ArabW5egRdDmUxOL5ybL1hoFxQq3jbI5Ms+mELiGxbbTRVcHFKHwlubFbV5jKS59GJxaHHheGdGf4/eJQuofd6iZSvCSIrkFUUYuX8ssXT73qmqKojaeuLerfrTj1h0zViWX8oNrW91DVfzFFamzq0Y3I7r0TukVQS4LwswmAoL9qt3X403O3bu0PPnjniGz0wSnMup7oXFmvaQwavmIxF1nLIA1r1BtYzWWuk7XftIZhaY2+bu1dBa3WsMUatWeN7Nbr4H/r7Q++Wm5700T+Zz/ge2Flhx7UjDIlqR7VMRh2y/v7zT9G5oAu";
        $menHead = "BvqP7b1Tfi";
        $womenHead = "epM1RMX9DC";

        $avatar = Avatars::create(
            [
                'firstName' => $avatarFName,
                'gender' => $gender,
                'lastName' => $avatarLName,
                'fullName' => $avatarFName . $avatarLName,
                'nameInstance' => 1,
                'defaultAvatar' => $user->id,
                'takePet' => false,
                'dateCreated' => date_create('now', null),
                'config' => ($gender == 'M') ? $menConfig : $womenConfig,
                'headPostfix' => ($gender == 'M') ? $menHead : $womenHead,
                'snapshotPostfix' => ($gender == 'M') ? $menHead : $womenHead,
                'thumbUrl' => ($gender == 'M') ? $avatar_url.'/'. $menHead .'_thumb.png': $avatar_url.'/'. $womenHead.'_thumb.png',
                'snapUrl' => ($gender == 'M') ? $avatar_url.'/'. $menHead .'_snap.png': $avatar_url.'/'. $womenHead.'_snap.png',


            ]
        );
        // Default Homespace
        $space_name = $avatar->firstName . "'s " .$avatar->lastName."'s ". "House";
                    
        // }
        $desc = spaceModels::where('model_id', 2638)->pluck('model_desc')->first();
        $space = avatarSpaces::create
        ([
            'avatar_id' => $avatar->avatar_id,
            'name' => $space_name,
            'modelId' => 2638,
            'config' => "",
            'desc' => $desc
        ]
        );
        // Home space ? true else false... update value
        $homeID = DB::getPdo()->lastInsertId();

        Avatars::where('avatar_id', $avatar->avatar_id)->update(['homeSpaceId' => $homeID]);
        // end Default Homespace
        createItems(create_guid(), 1, 1830, $user->id);
        createItems(create_guid(), 1, 1811, $user->id);
        createItems(create_guid(), 1, 1793, $user->id);
        createItems(create_guid(), 1, 3924, $user->id);
        createItems(create_guid(), 1, 2091, $user->id);
        createItems(create_guid(), 1, 2092, $user->id);
        createItems(create_guid(), 1, 2093, $user->id);
        createItems(create_guid(), 1, 2094, $user->id);
        createItems(create_guid(), 1, 2095, $user->id);
        createItems(create_guid(), 1, 2096, $user->id);
        createItems(create_guid(), 1, 2097, $user->id);
        createItems(create_guid(), 1, 2098, $user->id);
        createItems(create_guid(), 1, 2099, $user->id);
        createItems(create_guid(), 1, 2100, $user->id);
        createItems(create_guid(), 1, 2101, $user->id);
        createItems(create_guid(), 1, 2102, $user->id);
        createItems(create_guid(), 1, 2103, $user->id);
        createItems(create_guid(), 1, 2104, $user->id);

        createItems(create_guid(), 1, 5604, $user->id);
        createItems(create_guid(), 1, 5605, $user->id);
        createItems(create_guid(), 1, 5606, $user->id);
        createItems(create_guid(), 1, 5607, $user->id);
        createItems(create_guid(), 1, 5608, $user->id);
        createItems(create_guid(), 1, 5609, $user->id);
        createItems(create_guid(), 1, 5610, $user->id);
        $amf->data = $user->id;
        $amf->success = true;
    }
    elseif($request->fails())
    {
        $messages = $request->messages();
        if ($messages->has('email'))
            $amf->code = 200;
            $amf->success = false;
        // var_dump($messages);
    }
        // $amf->success = false;
        return $amf;

    }


    function searchU($userID, $fName, $lName)
    {
        // $user = new functions();
        // if ($aid == null)
        //     $aid = $uid;
        if ($userID == null)
        {
            $avatar = Avatars::where('fullName', 'LIKE', '%'.$fName. $lName.'%')->get()->first();
            $count = count($avatar);
            // $userID = $avatar->first()->avatar_id;
            $user = Users::where('id', $userID )->get();

        }
        else
        {
            $user = Users::where('id', $userID )->get();
            $avatar = Avatars::where('avatar_id', $user[0]->choosenAvatar)->get()->first();
            $count = count($user);
        }
        //loop($count, $spaces);
        // var_dump($count);
        $arr = array();
        for ($i = 0; $i < $count; $i++)
        {
            $arr[] = array
            (
                'id' => $user[$i]["id"],
                'tokens' => $user[$i]['tokenBalance'],
                'gold' => $user[$i]['goldBalance'],
                'cp' => 0,
                'kudosPoints' => 0,
                'kudosWeek' => "1",
                'user' => array   // UserSMIINFO
                (
                    'id' => strval($user[$i]["id"]),
                    'firstName' => $user[$i]["firstName"],
                    'lastName' => $user[$i]["lastName"],
                    'emailAddress' => $user[$i]["email"],
                    'payingUser' => true,
                    //   'warningMessage' => "",
                    'buttonColor' => "",
                    'buttonReason' => ""
                ),
                // AVATAR SMI INFO
                'defaultAvatar' => array
                (
                    'id' => $avatar->avatar_id,
                    'nameInstance' => $avatar->nameInstance,
                    'lastName' => $avatar->lastName,
                    'firstName' => $avatar->firstName,
                    'hasPet' => false,
                    'isOnline' => false,
                    'isDefault' => true,
                ),
            );
        }
        return $arr;
    }

    function getOnlineStaff()
    {
        $amf = new stdClass();
        $arr = array();
        $online = onlineUsers::where('online', true)->get();
        foreach ($online as $key => $value)
        {
            $avi = Avatars::where('avatar_id', $value->avatar_id)->get()->first();
            $user = Users::where('id', $avi->owner_id)->get()->first();
            $staffGroups = array(1,2,13);
            // if $user->primaryGroupId contains staffGroups id
            if (in_array($user->primaryGroupId, $staffGroups))
            {
                $arr[] = array(
                    'id' => (float)$user->id,
                    'avatarId' => $avi->avatar_id,
                    'fName' => $user->firstName,
                    'lName' => $user->lastName,
                    'email' => $user->email,
                    'spaceId' => (float) onlineUsers::where('avatar_id', $avi->avatar_id)->get()->first()->space_id,
                    'groupName' => userGroups::where('id', $user->primaryGroupId)->get()->first()->name,
                    'spaceDesc' => avatarSpaces::where('id', onlineUsers::where('avatar_id', $avi->avatar_id)->get()->first()->space_id)->get()->first()->name,
                    'avatarFName' => $avi->firstName,
                    'avatarLName' => $avi->lastName,
                    'groupId' => (float)$user->primaryGroupId,
                );
            }
        }
        $amf->recordSet = $arr;
        $amf->success=true;
        return $amf;
    }

    function getUserDetails($timeconfig, $uid)
    {
        $user = Users::where('id', $uid )->get()[0];
        $amf = new stdClass();
        $arr = array();
        if ($user->secondaryGroupIds != null)
        {
            $groups = explode(',', $user->secondaryGroupIds);
            $count = count($groups);
            for ($i = 0; $i < $count; $i++)
            {
                // $group = Groups::where('groupId', $groups[$i])->get()[0];
                $arr= $groups;
            }
         
            // var_dump($user->secondaryGroupIds);
        }
        $amf->id = $user["id"];
        $amf->tokens = $user['tokenBalance'];
        $amf->gold = $user['goldBalance'];
        $amf->numAvatars = 1;
        $amf->useAuthenticator = false;
        $amf->secretQuestionId = "";
        $amf->secretAnswer = "I didn't save this info";
        $amf->fName = $user['firstName'];
        $amf->lName = $user['lastName'];
        $amf->modNotes = "Naughty Player";
        $amf->password = '1234';
        $amf->gender = $user['sex'];
        $amf->source = $user['goldBalance'];
        $amf->authenticatorConfirmed = false;
        $amf->kudosPoints = 0;
        // $amf->dob = "";
        $amf->emailAddress = $user['email'];
        $amf->kudosWeek = "1";
        $amf->cp = 0;
        $amf->primaryUserGroupId = $user['primaryGroupId'];
        $amf->ipHistory = array();
        $amf->additionalUserGroupIds = $arr;
        // $amf->cp = $user['citizenLevel'];
        $amf->success=true;
        return $amf;
    }

    function getUserSMIDetails($timconfig, $aid)
    {
        $user = Users::where('id', $aid )->get()[0];
        $avatar = Avatars::where('avatar_id', $user->defaultAvatar)->get()[0];
        $amf = new stdClass();
        $amf->id = $user["id"];
        $amf->tokens = $user['tokenBalance'];
        $amf->gold = $user['goldBalance'];
        $amf->numAvatars = 1;
        $amf->success=true;
        $amf->onlineAvatar = array
        (
            'id' => strval($user["id"]),
            'nameInstance' => $avatar['nameInstance'],
            'lastName' => $avatar['lastName'],
            'firstName' => $avatar['firstName'],
            'hasPet' => false,
            'isOnline' => false,
            'isDefault' => true,
        );
        return $amf;
    }

    function findUser($timeconfig, $uid)
    {
        $user = Users::where('id', $uid)->get();
        // var_dump($spaces[0]);
        $amf = new stdClass();
        if ($user[0]['id'] != null)
        {

            $amf->id = $user[0]['id'];
            $amf->desc = $user[0]['firstName'] . " " . $user[0]['lastName'];
            $amf->success=true;
        }
        else
            $amf->success=false;
        return $amf;
    }
}

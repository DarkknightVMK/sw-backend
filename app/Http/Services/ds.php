<?php
use App\Models\Users;
use App\Models\Avatars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\userGroups;
use Illuminate\Support\Facades\Hash;
use App\Models\actionbar;
use App\Models\missions;
use App\Models\missionTasks;
use App\Models\avatarMissions;
use App\Models\avatarSpaces;
use App\Models\onlineUsers;
use Carbon\Carbon;
// use App\result\ErrorCodes;
use App\result\MissionKeys;
use App\Models\avatarMissionKeys;
use App\Http\Services\functions;
use App\Models\pets;
// use App\result\ServiceResult;
use App\Models\Messages;
use App\Models\messagesSent;
use App\result\ServiceResult;
use App\result\ErrorCodes;
use SabreAMF\Client;
use App\Models\items;
use App\Models\avatarItems;

// include_once('functions.php');
// include_once('../Classes/ErrorCodes.php');

class ds 
{
    public $user;
    // public $spaceId;
    

    public function logout()
    {
        Auth::logout();
        session()->flush();
        // return redirect('/');
    }
    public function getUser($json = null)
    {
        $user_json = Users::where('id', session('user'))->get();
        $user_json = $user_json->toJson();
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $user_json, $jsondecode);
        $user = json_decode($jsondecode[0][0], true);
        if ($json == null)
            return $user;
        return $user[$json];
    }

    public function saveUserPersonal($timeconfig, $email, $currPass, $newPass,$firstName, $lastName,$gender,$birthday)
    {
        $amf = new stdClass();

        // if($currPass != null && $newPass != null)
        // {
        //     if(Hash::check($currPass, functions::getUser('password')))
        //     {
        //         //valid pw
        //         Users::where('id',  functions::getUser('id'))->update([
        //             'password' => bcrypt($newPass),
        //           ]);
        //         $amf->success = true;
        //     }

        //         $amf->success = false;
        //         $amf->code = 303;

        // }
        // else

        if ($currPass == "" && $newPass == "" && $email != null && $firstName != null && $lastName != null && $gender != null)
        {

            // check if email is valid
             if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // invalid emailaddress
                $amf->success = false;
                $amf->code = 400;
                return $amf;
            }

            Users::where('id', functions::getUser('id'))->update([
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'sex' => $gender
            ]);
            $amf->success = true;
        }
        if(Hash::check(strval($currPass), functions::getUser('password')))
                {
                    //valid pw
                    Users::where('id',  functions::getUser('id'))->update([
                        'password' => bcrypt($newPass),
                      ]);
                    $amf->success = true;
                    return $amf;
                }
        // elseif($currPass != null && $newPass != null)
        // {

            // if()
            // {
            //     //valid pw
            //     // Users::where('id',  functions::getUser('id'))->update([
            //     //     'password' => bcrypt($newPass),
            //     //   ]);

            // }

                // $amf->success = false;
                // $amf->code = 303;

        // }
        $amf->success = false;
        $amf->code = 2000;

        return $amf;
    }

    public function createAvatarGroup($timeconfig, $groupName, $desc, $security, $tags, $extraTags, $website)
    {
        $amf = new stdClass();

        if ($extraTags != null)
        {
            $tag = $tags . ', ' . $extraTags;
        }
        else
        {
            $tag = $tags;
        }
        if (userGroups::create([
            'uid' => intval(session('user')),
            'name' => $groupName,
            'description' => $desc,
            'type' => $security,
            'tags' => $tag,
            'website' => $website,
            'active' => true,
            'indexed' => true
        ])){
        $amf->success = true;}
        else{
            $amf->success = false;
        }
        return $amf;

    }
    function viewMyAvatarGroupsConcise()
    {
        $uid = functions::getUser('id');
        $userGroup = userGroups::where('uid', $uid)->get();
        $amf = new stdClass();
        $arr[] = array(
            'avatargroup_id|INT UNSIGNED|avatargroup',
            'avatargroup_name|VARCHAR|avatargroup',
            'avatargroup_access_control|CHAR|avatargroup',
            'avatargroupr_role|CHAR|avatargroupr'
        );
        for ($i = 0; $i < count($userGroup); $i++) {
            $arr[] = array (
                $userGroup[$i]['id'],
                $userGroup[$i]['name'],
                'O',
                'O'
            );
        }
        $amf->recordSet = $arr;
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = count($userGroup) -1;
        $amf->success = true;
        return $amf;
    }
    public function viewMyAvatarGroups($timeconfig, $unk, $offset, $limit)
    {
        $uid = functions::getUser('id');
        $userGroup = userGroups::where('uid', $uid)->get();
        $amf = new stdClass();
        $arr[] = array (
            'group_banned',
            'avatargroup_desc',
            'avatargroup_id',
            'avatargroup_timestamp',
            'avatargroup_member_count',
            'avatargroup_tags',
            'avatargroup_access_control',
            'avatargroup_name',
            'group_role',
            'avatargroup_icon_url',
            'avatargroup_website',
        );

        for ($i = 0; $i < count($userGroup); $i++) {
            $arr[] = array (
                0,
                $userGroup[$i]->description,
                 $userGroup[$i]['id'],
                '',
                1,
                $userGroup[$i]->tags,
                'S',
                $userGroup[$i]->name,
                'M',
                'groups/icon_group_smallworlds.png',
                ''
            );

        }
        $amf->recordSet = $arr;
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = count($userGroup) -1;
        $amf->success = true;
        return $amf;
    }

    public function flashLogin($email, $password, $remember)
    {
        $amf = new stdClass();
        if (Auth::attempt(['email' => $email, 'password' => $password], $remember))
        {
            $amf->success = true;
        }
        else
        {
            $amf->success = true;
        }
        return $amf;
    }
    // public function getLoggedInUser()
    // {
    //     $amf = new stdClass();
    //     $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
    //     $amf->$explicitTypeField = "com.smallworlds.service.result.user.LoginResult";
    //     if (Auth::check()) {
    //         $avatar = new avatar();
    //         $amf->fname = functions::getUser('firstName');
    //         $amf->lname = functions::getUser('lastName');
    //         $primaryGroup = functions::getUser('primaryGroupId');
    //         $secondaryGroups = functions::getUser('secondaryGroupIds');
    //         if ($secondaryGroups == null)
    //         {
    //             $group = userGroups::where('id', $primaryGroup)->pluck('permissionId');
    //             $group2 = str_replace('["', '', $group);
    //             $group3 = str_replace('"]', '', $group2);
    //             $pid = explode(', ', strval($group3));

    //             foreach ($pid as $perm) {
    //                 $arr[$perm] = true;
    //             }
    //             $amf->permissions = $arr;

    //         }
    //         else{
    //             //figure out secondary group permissions
    //         }
    //         $amf->success = true;

    //     }
    //     $amf->success = false;
    //     return $amf;
    // }

    public function viewMyAvailableEvents()
    {
        $amf = new stdClass();
        // $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        // $amf->$explicitTypeField = "com.smallworlds.service.result.event.EventResult";
        $amf->recordSet = array(
            array(
                'event_id',
                'event_name',
                'event_description',
                'event_start_date',
                'event_end_date',
                'event_location',
                'event_location_lat',
                'event_location_long',
                'event_location_zoom',
                'event_location_address',
                'event_location_city',
            ));
        $amf->success = true;
        return $amf;
    }

    public function storeActionBarItem($timeconfig, $row, $col, $type, $source, $name, $label, $cid )
    {
        $amf = new stdClass();
        if ($timeconfig != null && $row != null && $col != null && $type == null && $source == null && $name == null && $label == null && $cid == null)
        {
            // delete any entry in this row and column
            if (actionbar::where('actionbaritem_row', $row)->where('actionbaritem_column', $col)->where('actionbaritem_user_id', functions::getUser('id'))->delete())
            {
                $amf->success = true;
            }
            else
            {
                $amf->success = true;
            }
            return $amf;
        }

        if (actionbar::where('actionbaritem_name', $name)->exists())
        {
            // update row and col
            actionbar::where('actionbaritem_name', $name)->update(array('actionbaritem_row' => $row, 'actionbaritem_column' => $col));
        }
        else
        {
            if (actionbar::create(array(
                'actionbaritem_row' =>(float) $row,
                'actionbaritem_column' => (float) $col,
                'actionbaritem_type' => $type,
                'actionbaritem_url' => $source,
                'actionbaritem_name' => $name,
                'actionbaritem_label' => $label,
                'actionbaritem_cid' => $cid,
                'actionbaritem_user_id' => functions::getUser('id')
            )))
            {
                $amf->success = true;
            }
            else
            {
                $amf->success = false;
            }
        }
        $amf->success = true;
        return $amf;
    }
    public function getLoggedInUser($timconfig = null, $param2 = null) {

//        if (!isset($_SESSION['user'])) {
//            $user_json = User::where('id', $_SESSION["uid"])->get();
//            $user_json = $user_json->toJson();
//            preg_match_all('/\{(?:[^{}]|(?R))*\}/', $user_json, $jsondecode);
//            $user = json_decode($jsondecode[0][0], true);
//            $avatar_json = Avatars::where('avatar_id', $_SESSION["uid"])->get();
//            $avatar_json = $avatar_json->toJson();
//            preg_match_all('/\{(?:[^{}]|(?R))*\}/', $avatar_json, $json2decode);
//            $avatar = json_decode($json2decode[0][0], true);
//
//            $_SESSION['avatar'] = $avatar;
//            $_SESSION['user'] = $user;
//        }
//        else{
//            $user = $_SESSION['user'];
//            $avatar = $_SESSION['avatar'];
//        }


        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.user.LoginResult";
        if (!functions::checkSession())
        {
            $amf->success = false;
            $amf->code = ErrorCodes::SESSION_EXPIRED;
        }
        elseif (session('avatar')) {
            $avatar = new functions();
//             $dob =  amf_decode((float)functions::getUser('created_at'));
            // $amf->test = functions::getUser();
            $amf->chatFilter = false;
            if (pets::where('ownerId', session('avatar'))->exists())
                $amf->petActive = boolval(pets::where('ownerId', session('avatar'))->first()->active);
            else
                $amf->petActive = false;
            $amf->source = '';
            $amf->completedSignUp = true;
            $amf->colorRoomChat = "FFFFFF";
            $amf->colorWebChat = "FFFFFF";
            $amf->isVIP = true;
            $amf->gameChat = true;
            $amf->avatarCount = 1;
            $amf->avatarName = functions::getAvatar('firstName') . ' ' . functions::getAvatar('lastName');
            // $amf->test2 = $avatar;
            $amf->citizenPoints = (float) functions::getUser('citizenPoints');
            $amf->citizenLevel = (float)functions::getUser('citizenLevel');

            $amf->overrideCPRank =(float) functions::getAvatar('overrideCPRankMinCL') ;
            $amf->authenticatorForced = false;
            $amf->settings = $this->getSettings();
            $amf->selectedCPTheme = 'royal';
            $amf->gender = functions::getUser('sex');
            $amf->sex = functions::getUser('sex');
            // $amf->citizenLevel = functions::getUser('citizenLevel');
            $amf->streamMedia = true;
            // $amf->currentPetId = null;
            if (pets::where('ownerId', session('avatar'))->exists())
            {
                if (pets::where('ownerId', session('avatar'))->first()->active)
                    $amf->currentPetId = pets::where('ownerId', session('avatar'))->first()->stringId;
                else
                    $amf->currentPetId = null;
            }
                else
                $amf->currentPetId = null;
            // $amf->choosenAvatarName = "Justin";
            $amf->userId = strval(functions::getUser('id'));
            $primaryGroup = functions::getUser('primaryGroupId');
            $secondaryGroups = functions::getUser('secondaryGroupIds');
            if ($primaryGroup != null)

            // if ($secondaryGroups == null)
            {
                $group = userGroups::where('id', $primaryGroup)->pluck('permissionId');
                $group2 = str_replace('["', '', $group);
                $group3 = str_replace('"]', '', $group2);
                $pid = explode(', ', strval($group3));

                foreach ($pid as $perm) {
                    $arr[$perm] = true;
                }
                $amf->permissions = $arr;

            }
            else{
                //figure out secondary group permissions
            }
            $amf->tokens = (float) functions::getUser('tokenBalance');
            $amf->hasReceivedEmailVerificationRewards = false;
            $amf->gold = (float) functions::getUser('goldBalance');
            $amf->firstName = functions::getUser('firstName');

            // beta
            $amf->isNewbie = false;

            $amf->fname = functions::getUser('firstName');
            $amf->lname = functions::getUser('lastName');
            $amf->email = functions::getUser('email');
            $amf->agreedToLicense = true;
            $amf->affliateScript = null;
            $amf->webChat = true;
            $amf->noSecretQuestion = true;
            $amf->verifiedEmail = true;
            $amf->verifiedEmailBefore = true;
            $amf->affiliatePingUrl = '';
            $amf->tipRules = '';
            $amf->dobMonth = 1;
            $amf->dobDate = 18;
            $amf->dobYear = 1990;
            $amf->acctBalance = (float) functions::getUser('goldBalance');
            $amf->receiveGeneralNotifications = false;
            $amf->receiveInWorldMailNotifications = false;
            $amf->receiveGiftNotifications = false;
            $amf->receiveEmails = false;
            $amf->noPassword = false;
            $amf->avatarCount = 1;
            
            // return Date Object
            $amf->serverTime = new Amfphp_Core_Amf_Types_Date( Carbon::now()->valueOf());


            $amf->dateOfBirth = new Amfphp_Core_Amf_Types_Date( Carbon::createFromTimestamp(strtotime(functions::getUser('dob_month') + 1 .'/'.functions::getUser('dob_day').'/'.functions::getUser('dob_year')))->valueOf());
            // end beta
            $amf->acctBalance = functions::getUser('goldBalance');
            $amf->createdDate =  new Amfphp_Core_Amf_Types_Date( Carbon::createFromTimestamp(strtotime(functions::getUser('created_at')))->valueOf());
            $amf->lastName = functions::getUser('lastName');
            $amf->emailAddress = functions::getUser('email');
            $amf->isEmailVerified = true;

            $amf->currentAvatarId = strval(functions::getUser('defaultAvatar'));//$this->create_guid();
            $amf->choosenAvatarId = strval(functions::getUser('defaultAvatar'));//$this->create_guid();

            $amf->sessionId = session('id');
            $amf->actionBarItems = $this->getActionBarItems();
            $amf->noPassword = false;
            $amf->success = true;
            $amf->authenticatorConfirmed = false;
            $amf->hasAuthenticator = false;

            $amf->isFacebookConnected = false;
//        if (isset($sesh))
//        {
//            return User::all()->toJson();
//        }
        }
        else {
            $amf->success = false;
        }
        return $amf;
    }

//    function getLoggedInUser()
//    {
//        $amf = new stdClass();
//        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
//        $amf->$explicitTypeField = "com.smallworlds.service.result.userauth.LoginResult";
//        $amf->success = false;
//        return $amf;
//    }
    function getSettings() {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.userauth.settings.external.amf.result.UserSettingsResult";
        $amf->receiveFriendRequestEmails = true;
        $amf->receiveInWorldMailEmails = true;
        $amf->receiveGiftReceivedEmails = true;
        $amf->loadtimeRUFrequency = (float) 0.1;
        $amf->receivePromotionalEmails = true;
        $amf->matureLanguageFilter = false;
        $amf->disableStreamingMusic = false;
        $amf->loadTimeFTUFrequency = (float) 1;
        $amf->success = true;
        return $amf;
    }

    function loginNewUser()
    {
        $amf = new stdClass();
        $amf->success = true;
        return $amf;
    }
    function getActionBarItems()
    {
        $amf = new stdClass();
        $arr = array();
        foreach (actionbar::where('actionbaritem_user_id', functions::getUser('id'))->get() as $item)
        {
            $arr[] = $item->toArray();
        }
        // $amf->actionbaritem_name = "ae508ba00022de487e1190340cb7655b";
        // $amf->actionbaritem_column = 1;
        // $amf->actionbaritem_id = "121863927";
        // $amf->actionbaritem_url = "items/base/consumables/emotes/emote_01_smile_item.xml";
        // $amf->actionbaritem_label = Null;
        // $amf->actionbaritem_user_id = functions::getUser('id');
        // $amf->actionbaritem_cid = "com.smallworlds.entity.item.SpriteItem";
        // $amf->actionbaritem_row = 3;
        // $amf->actionbaritem_type = "C";
//        $amf->actionbaritem_timestamp = time();
        return $arr;

    }

    function viewAvatarGroupSpaceMembers($timeconfig, $spaceID)
    {
        $amf = new stdClass();
        $amf->recordSet = array(array("avatargroupspacemember_id|INT UNSIGNED|avatargroupspacemembe", "avatargroupspacemember_avatargroup_id|INT UNSIGNED|avatargroupspacemember", "avatargroupspacemember_role|CHAR|avatargroupspacemember", "avatargroup_name|VARCHAR|avatargroup"));
        $amf->startIndex = 0;
        $amf->maxLength = 0;
        $amf->totalCount = 0;
        $amf->success = true;
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

    function ping()
    {
        $amf=new stdClass();
        // $session = new functions();

        //check expires at
        if (!functions::checkSession())
        {
            $amf->success = false;
            $amf->code = ErrorCodes::SESSION_EXPIRED;
        }
        else
            $amf->success = true;
        return $amf;
    }
    function viewUniversalTagClientScriptOfWorld()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.StringResult";
        $amf->data = "tags/smallworlds_universaltagset_script.as.comp";
        $amf->success =true;
        return $amf;
    }

    /**
    *  Missions
    *  mission is referred to as tasks in the game
    *  mission chains are the actual mission
    */

    function viewFeaturedMissionChains($timeconfig, $filter, $skip, $take)
    {
        //TODO
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";

        if ($filter != null)
            $missions = missions::where('goodMission', true)->where('creatorAvatarId', $filter)->get();
        else
            $missions = missions::where('goodMission', true)->get();
        // var_dump($missions);
            $avatar = new functions();

            // Result must be in this format


            
            $arr[] = MissionKeys::featuredMissions;  
            foreach ($missions as $mission)
            {
                $space = avatarSpaces::where('id', $mission->lastActivatedSpaceId)->first();
                $arr[] = array(
                    // MissionChain
                    $mission->desc, // 
                    (float)$mission->xpLevelTypeId, //xp leveltype id
                    (float)$mission->minXPLevel, //min xp level
                    $mission->lastActivatedSpaceId, //
                    $mission->creatorAvatarId, //$missions->missionchain_entry_tokens,
                    $mission->activationGroup, //$mission->activationgroup,
                    $mission->followOnMissionId,
                    $mission->panelStyle, //$missions->average_time,
                    $mission->followOnAutomatic,
                    ($mission->expires == 1) ? 'Y' : 'N', //expires,
                    $mission->totalPlays,
                    $mission->title,
                    $mission->completedDesc,
                    $mission->votes,
                    ($mission->goodMission == 1) ? 'Y' : 'N',
                    $mission->rating,
                    $mission->id,
                    $mission->bonusModelId,
                    $mission->entryTokens,
                    $mission->bonusTokens,
                    $mission->timestamp,
                    $mission->firstPlayed,
                    $mission->totalPlayTime,
                    $mission->creatorUserId,
                    $mission->guaranteedXP,
                    //Spaces
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'spaceThumbnailSource') : null,
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'modelId') : null,
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'name') : null,
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'accessControl') : null,
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'user_id') : null,
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'icon') : null,
                    "", // space role access

                    $avatar->getAvatarByID($mission->creatorAvatarId, 'firstName'),
                    $avatar->getAvatarByID($mission->creatorAvatarId, 'lastName'),
                    $avatar->getAvatarByID($mission->creatorAvatarId, 'headPostfix'), //$missions->creator_avatar_head_postfix,
                    $avatar->getAvatarByID($mission->creatorAvatarId, 'headPostfix'), 
                    $avatar->getAvatarByID($mission->creatorAvatarId, 'snapshotPostfix'),
                    $avatar->getAvatarByID($mission->creatorAvatarId, 'nameInstance'),
                    $mission->weightedPlayTime, // average time
                    null, // bonus rewards
                    null, // now time
                    null, //avg reward xp
                    null, //avg reward tokens

                );
            } 


        $amf->recordSet = $arr;
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }

    function viewMostRecentMissionChains($timeconfig, $filter, $aid, $featured, $skip, $take)
    {
        //TODO
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";

        if ($filter != null && $aid != null)
        { 
            switch($filter)
            {
                case "all":
                    if ($featured)
                        $missions = missions::where('goodMission', true)->where('creatorAvatarId', $aid)->orderBy('id', 'desc')->get();
                    else
                        $missions = missions::where('creatorAvatarId', $aid)->orderBy('id', 'desc')->get();
                    break;
                case "1" :
                    // artist levels
                    if ($featured)
                        $missions = missions::where('goodMission', true)->where('creatorAvatarId', $aid)->where('xpLevelTypeId', 1)->orderBy('id', 'desc')->get();
                    else
                        $missions = missions::where('creatorAvatarId', $aid)->where('xpLevelTypeId', 1)->orderBy('id', 'desc')->get();
                    break;
                case "2" :
                    // explorer levels
                    if ($featured)
                        $missions = missions::where('goodMission', true)->where('creatorAvatarId', $aid)->where('xpLevelTypeId', 2)->orderBy('id', 'desc')->get();
                    else
                        $missions = missions::where('creatorAvatarId', $aid)->where('xpLevelTypeId', 2)->orderBy('id', 'desc')->get();
                    break;
                case "3" :
                    // gamer levels
                    if ($featured)
                        $missions = missions::where('goodMission', true)->where('creatorAvatarId', $aid)->where('xpLevelTypeId', 3)->orderBy('id', 'desc')->get();
                    else
                        $missions = missions::where('creatorAvatarId', $aid)->where('xpLevelTypeId', 3)->orderBy('id', 'desc')->get();
                    break;
                case "4" :
                    // social levels
                    if ($featured)
                        $missions = missions::where('goodMission', true)->where('creatorAvatarId', $aid)->where('xpLevelTypeId', 4)->orderBy('id', 'desc')->get();
                    else
                        $missions = missions::where('creatorAvatarId', $aid)->where('xpLevelTypeId', 4)->orderBy('id', 'desc')->get();
                    break;
            }
        }
        else if ($filter != null)
        {
            switch($filter)
            {
                case "all":
                    if ($featured)
                        $missions = missions::where('goodMission', true)->orderBy('id', 'desc')->get();
                    else
                        $missions = missions::orderBy('id', 'desc')->get();
                    break;
                case "1" :
                    // artist levels
                    if ($featured)
                        $missions = missions::where('goodMission', true)->where('xpLevelTypeId', 1)->get();
                    else
                        $missions = missions::where('xpLevelTypeId', 1)->orderBy('id', 'desc')->get();
                    break;
                case "2" :
                    // explorer levels
                    if ($featured)
                        $missions = missions::where('goodMission', true)->where('xpLevelTypeId', 2)->get();
                    else
                        $missions = missions::where('xpLevelTypeId', 2)->orderBy('id', 'desc')->get();
                    break;
                case "3" :
                    // gamer levels
                    if ($featured)
                        $missions = missions::where('goodMission', true)->where('xpLevelTypeId', 3)->get();
                    else
                        $missions = missions::where('xpLevelTypeId', 3)->orderBy('id', 'desc')->get();
                    break;
                case "4" :
                    // social levels
                    if ($featured)
                        $missions = missions::where('goodMission', true)->where('xpLevelTypeId', 4)->get();
                    else
                        $missions = missions::where('xpLevelTypeId', 4)->orderBy('id', 'desc')->get();
                    break;
            }
        }
        //     $missions = missions::where('goodMission', true)->where('creatorAvatarId', $filter)->get();
        // else
        //     $missions = missions::where('goodMission', true)->get();
        // var_dump($missions);
            $avatar = new functions();

            // Result must be in this format


            
            $arr[] = MissionKeys::featuredMissions;  
            foreach ($missions as $mission)
            {
                $space = avatarSpaces::where('id', $mission->lastActivatedSpaceId)->first();
                $arr[] = array(
                    // MissionChain
                    $mission->desc, // 
                    (float)$mission->xpLevelTypeId, //xp leveltype id
                    (float)$mission->minXPLevel, //min xp level
                    $mission->lastActivatedSpaceId, //
                    $mission->creatorAvatarId, //$missions->missionchain_entry_tokens,
                    $mission->activationGroup, //$mission->activationgroup,
                    $mission->followOnMissionId,
                    $mission->panelStyle, //$missions->average_time,
                    $mission->followOnAutomatic,
                    $mission->expires,
                    $mission->totalPlays,
                    $mission->title,
                    $mission->completedDesc,
                    $mission->votes,
                    ($mission->goodMission == 1) ? 'Y' : 'N',
                    $mission->rating,
                    $mission->id,
                    $mission->bonusModelId,
                    $mission->entryTokens,
                    $mission->bonusTokens,
                    $mission->timestamp,
                    $mission->firstPlayed,
                    $mission->totalPlayTime,
                    $mission->creatorUserId,
                    $mission->guaranteedXP,
                    //Spaces
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'spaceThumbnailSource') : null,
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'modelId') : null,
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'name') : null,
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'accessControl') : null,
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'user_id') : null,
                    ($mission->lastActivatedSpaceId != null) ? functions::getSpace( $mission->lastActivatedSpaceId, 'icon') : null,
                    "", // space role access

                    $avatar->getAvatarByID($mission->creatorAvatarId, 'firstName'),
                    $avatar->getAvatarByID($mission->creatorAvatarId, 'lastName'),
                    $avatar->getAvatarByID($mission->creatorAvatarId, 'headPostfix'), //$missions->creator_avatar_head_postfix,
                    $avatar->getAvatarByID($mission->creatorAvatarId, 'headPostfix'), 
                    $avatar->getAvatarByID($mission->creatorAvatarId, 'snapshotPostfix'),
                    $avatar->getAvatarByID($mission->creatorAvatarId, 'nameInstance'),
                    $mission->weightedPlayTime, // average time
                    null, // bonus rewards
                    null, // now time
                    null, //avg reward xp
                    null, //avg reward tokens

                );
            } 


        $amf->recordSet = $arr;
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }

    function viewMyChannels()
    {
        return new ServiceResult();
    }

    function viewMyCustomMissionChains()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";
        
        if (missions::where('creatorAvatarId', functions::getAvatar('avatar_id'))->count() > 0) 
        {
            $missions = missions::where('creatorAvatarId', functions::getAvatar('avatar_id'))->get();
            $avatar = new functions();

            // Result must be in this format
            $arr[] = MissionKeys::missionInfo;             

            foreach ($missions as $mission) 
            {
                $missionTask = missionTasks::find($mission->id);

             (avatarMissions::where('missionId', $mission->id)->count() != null) ? $avatarMission = avatarMissions::where('missionId', $mission->id)->first() : $avatarMission = null;
                $arr[] = array(
                    $mission->desc,
                    1, // active 1 or 0
                    $avatar->getAvatar('firstName'), // change to avatar id of the mission creator
                    // $avatar->getAvatar('headPostfix'),
                    $mission->xpLevelTypeId,
                    $mission->minXPLevel,
                    $mission->entryTokens,
                    $avatar->getAvatar('lastName'),
                    $mission->activationGroup,
                    $avatar->getAvatar('nameInstance'),
                    $mission->followOnMissionId,
                    // $avatar->getAvatar('headPostfix'),
                    // $avatar->getAvatar('snapshotPostfix'),
                    $mission->panelStyle,
                    ($mission->followOnAutomatic) ? 'Y' : 'N',
                    // null,
                    // // $mission->id, // avatarmission_mission_id
                    // null, // mission_time_left
                    // $missionTask->title, // mission_title
                    // $mission->id,
                    ($mission->expires) ? 'Y' : 'N',
                    $mission->completedDesc,
                    $mission->title,
                    strval($mission->id),
                    $mission->totalPlays,
                    $mission->rating,
                    $mission->votes,
                    $mission->weightedPlayTime, // average_time
                    1, // cooloff
                    -1, // missionchain_cooldown_time_override
                    0, // already_active
                    1,
                    0, // too_many_active_missions
                    '', // avatar_head_postfix
                    '', // avatar_thumb_postfix
                    '', // avatar_snapshot_postfix
                );
            }
            $amf->recordSet = $arr;

        }
        else
            $amf->recordSet = array();
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }

    function viewMyCustomMissionChainMissions($timeconfig, $missionID)
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";
        if (missionTasks::where('missionId', $missionID)->count() > 0) 
        {
            $tasks = missionTasks::where('missionId', $missionID)->get()->sortBy('orderNumber');
            $avatarMission = avatarMissions::where('missionId', $missionID)->first();
            $arr[] = array 
                (
                    'mission_missionchain_id',
                    'mission_desc',
                    'mission_completed_desc',
                    'mission_title',
                    'mission_time_left',
                    'mission_script',
                    'mission_id',
                );
            foreach ($tasks as $task) 
            {
                
                $arr[] = array(
                    $task->missionChainId,
                    $task->desc,
                    $task->completedDesc,
                    $task->title,
                    $task->timeLeft,
                    $task->script,
                    strval($task->id),
                );
            }
            $amf->recordSet = $arr;
        }
        else
            $amf->recordSet = array();
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }

    function viewMyCompletedMissionChains()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";
        $amf->recordSet = array();
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
        
    }
    function addMissionChain($timeconfig, $name, $desc, $completedDesc, $xpLevelTypeId, $entryTokens, $expires)
    {
        $amf = new stdClass();
        $missionChain = missions::create([
            'title' => $name,
            'desc' => $desc,
            'completedDesc' => $completedDesc,
            'expires' => boolval($expires),
            'entryTokens' => $entryTokens,
            'xpLevelTypeId' => $xpLevelTypeId,
            'creatorAvatarId' => functions::getAvatar('avatar_id'),
            'creatorUserId' => functions::getUser('id')
        ]);
        $amf->data = $missionChain->id; // <--- This is the ID of the mission entered from DB
        $amf->success=true;
        return $amf;
    }
    function saveMissionChain($timeconfig, $id, $tasks, $title, $desc, $completedDesc, $xpLevelType, $xpLevelMin, $entryTokens, $panelType, $followID, $autoStart, $expiry)
    {
        $amf = new stdClass();
        // $tasks is an array that needs to be sorted by the order of the tasks
        //update missions firstTaskId and lastTaskId
        // update missionTasks toTaskId and orderNumber
        if ($tasks != null)
        {
            $mission = missions::find($id);
            $mission->firstTaskId = $tasks[0];
            $mission->lastTaskId = $tasks[count($tasks)-1];
            $mission->save();
            
            $missionTasks = missionTasks::where('missionId', $id)->get();
            // figureout the order of the tasks and toTaskId   
            $order = 0;
            foreach ($tasks as $task) 
            {
                $missionTask = missionTasks::find($task);
                // toTaskId if last in array set to null
                if ($order == count($tasks)-1)
                    $missionTask->toTaskId = null;
                else
                    $missionTask->toTaskId = $tasks[$order+1];
                $order++;

                $missionTask->orderNumber = $order;
             
                $missionTask->save();
                

            }

            
            $task = missionTasks::where('missionId', $missionTask->missionId)->get();

                $missionTask->toTaskId = $task[$missionTask->orderNumber - 1]->id;
            
   
        }

        if ($followID == null)
            $autoStart = false;

        $missionChain = missions::find($id);
        $missionChain->title = $title;
        $missionChain->desc = $desc;
        $missionChain->completedDesc = $completedDesc;
        $missionChain->xpLevelTypeId = $xpLevelType;
        $missionChain->minXPLevel = $xpLevelMin;
        $missionChain->entryTokens = $entryTokens;
        $missionChain->panelStyle = $panelType;
        $missionChain->followOnMissionId = $followID;
        $missionChain->expires = boolval($expiry);
        $missionChain->followOnAutomatic = boolval($autoStart);
        $missionChain->save();
        $amf->success=true;
        return $amf;
    }
    function addMission($timeconfig, $id, $string, $taskName, $taskDesc, $taskComp, $script)
    {
        $amf = new stdClass();
        $mission = missions::where('id', $id)->get()->first();
        $missionTask = missionTasks::where('missionId', $id)->get()->last();
        if ($missionTask != null)
        {
            //There is a previous task in this mission 
            $orderNumber = $missionTask->orderNumber;
            //last inserted task for this mission
        }
        else
        {
            //There is no previous task in this mission
            $orderNumber = 0;
            //last inserted task for this mission
            $lastTask = null;
        }
        $task = missionTasks::create([
            'missionId' => $id,
            'title' => $taskName,
            'desc' => $taskDesc,
            'completedDesc' => $taskComp,
            'script' => $script,
            'active' => true,
            'orderNumber' => $orderNumber + 1,

        ]);

        if ($missionTask != null)
        {
            $missionTask->toTaskId = $task->id;
            $missionTask->save();
        }

        if ($mission->firstTaskId == null)
        {
            $mission->firstTaskId = $task->id;
            $mission->lastTaskId = $task->id;
        }
        else
            $mission->lastTaskId = $task->id;
        $mission->save();
        // $task->missionId = $id;
        // $task->title = $taskName;
        // $task->desc = $taskDesc;
        // $task->completedDesc = $taskComp;
        // $task->script = $script;
        // $task->save();
        $amf->data = $task->id;
        $amf->success=true;
        return $amf;
    }
    function saveMission($timeconfig, $id, $title, $taskDesc, $taskComp, $script)
    {
        $amf = new stdClass();
        $task = missionTasks::find($id);
        $task->title = $title;
        $task->desc = $taskDesc;
        $task->completedDesc = $taskComp;
        $task->script = $script;
        $task->save();
        $amf->success=true;
        return $amf;
    }
    function deleteMissionChain($timeconfig, $id)
    {
        // check if mission is active // todo
        $missionChain = missions::find($id);
        // check if missionsTask exists 
        // $missionTasks = missionTasks::where('missionId', $id)->get();

        if ( missionTasks::where('missionId', $id)->exists())
        {
            $missionTasks = missionTasks::where('missionId', $id)->get();
            foreach ($missionTasks as $missionTask)
            {
                $missionTask->delete();
            }
        }
        $missionChain->delete();
        if ($missionChain == null)
            return new ServiceResult(false, ErrorCodes::MISSION_NOT_FOUND);
        return new ServiceResult;

    }

    function deleteMission($timeconfig,$taskId)
    {
        $amf = new stdClass();
        
        $missionTask = missionTasks::find($taskId);
        $missionChain = missions::find($missionTask->missionId);
        $tasks = missionTasks::where('missionId', $missionTask->missionId)->get();
        if ($tasks !=null && $tasks->count() > 1)
        {
            //update order 
            foreach ($tasks as $task)
            {
                if ($task->orderNumber > $missionTask->orderNumber)
                {
                    $task->orderNumber = $task->orderNumber - 1;
                    $task->save();
                }
            }
           
            if ($missionTask->orderNumber == 1)
            {
                $missionChain->firstTaskId = $tasks[1]->id;
            }
            else if ($missionTask->orderNumber == $tasks->count())
            {
                $missionChain->lastTaskId = $tasks[$tasks->count() - 2]->id;
            }
            else
            {
                $missionTask->toTaskId = $tasks[$missionTask->orderNumber - 1]->id;
            }
            $missionTask->delete();
      
        }
        $amf->success=true;
        return $amf;
    }
    function cancelMission($timeconfig, $taskId, $unk)
    {
        if (avatarMissions::where('missionId',$taskId)->where('avatar_id', session('avatar'))->exists())
        {
            avatarMissions::where('missionId', $taskId)->where('avatar_id', session('avatar'))->update(['activated' => null]);
        }
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }

    function completeMission($timeconfig, $taskId, $location_vector)
    {
        $amf = new stdClass();
        $missionTask = missionTasks::find($taskId);
        $missionChain = missions::find($missionTask->missionId);
        $avatarMissions = avatarMissions::where('missionId', $taskId)->where('avatar_id', session('avatar'))->get()->first();
        $user = Users::find(session('user'));
        $isCapped = functions::reachedCap($missionChain->xpLevelTypeId);
        // keep track of xp rewarded for each path
        // if ($user->serverIP != '127.0.0.1')
        //   $client = new SabreAMF_Client("https://SITE_DOMAIN/java/swds/gateway;jsessionid=".session('id')); // Set up the client object
        // else
        //   $client = new SabreAMF_Client("https://SITE_DOMAIN/localhost/swds/gateway;jsessionid=".session('id'));

        if ($avatarMissions->isTesting)
        {
            $amf->chainComplete = true;

            $avatarMissions->delete();
        }

        elseif ($missionTask->toTaskId != null )
        {

            /*
            
            public var rewardsCapped:Boolean;
            public var completionTime:Number;
            public var rewardCPXP:int;
            public var rewardCPTokens:int;
            public var chainComplete:Boolean;
            public var rewardModelCID:String;
            public var rewardModelName:String;
            public var rewardXP:int;
            public var rewardTokens:int;
            public var unlockedMissionId:String;
            public var rewardModelId:String;
            public var rewardsCapCrossed:Boolean;
            public var rewardModelSource:String;
      */
      //update avatarMission spaceId to current spaceId
      $onlineUser = onlineUsers::where('avatar_id', session('avatar'))->get()->first();
        $avatarMissions->pickupSpaceId = $onlineUser->space_id;
        $avatarMissions->save();
            $amf->chainComplete = false;
            $amf->unlockedMissionId = $missionTask->toTaskId;
            // update missionId in avatarMissions
            avatarMissions::where('missionId', $taskId)->where('avatar_id', session('avatar'))->update(['missionId' => $missionTask->toTaskId]);
            //figure out the next task to give
        }
        elseif ($missionTask->toTaskId == null && $missionChain->followOnMissionId == null )
        {
            $amf->chainComplete = true;
            // $amf->unlockedMissionId = $missionChain->followOnMissionId;
            $avatarMission = avatarMissions::where('missionId', $taskId)->where('avatar_id', session('avatar'))->get()->first();
            // calc start time ($avatarMission->activated) - current time to timestamp using carbon   
            $startTime = Carbon::parse($avatarMission->activated);
            // $startTime = Carbon::parse($avatarMission->activated); 
            $endTime = Carbon::now();
            $timeDiff = $endTime->diffInSeconds($startTime);
            //$avatarMission->created_at to timestamp


            // $endTime = $startTime 
        
          ;
            // $avatarMission->completionTime = $avatarMission->created_at - time();

            // $time = now() - $avatarMission->created_at;
            $amf->completionTime = (float)$timeDiff;
            $amf->rewardsCapped = false;
            $amf->rewardCPXP = 0;
            $amf->rewardCPTokens = 0;
            if ($missionChain->guaranteedModelId != null)
            {
                $amf->rewardModelCID = items::where('model_id', $missionChain->guaranteedModelId)->get()->first()->model_cid;
                $amf->rewardModelId = (string)$missionChain->guaranteedModelId;  // TODO bonus
                $amf->rewardModelSource = items::where('model_id', $missionChain->guaranteedModelId)->get()->first()->model_source;
                // give item to avatar
                avatarItems::create(
                    [
                        'item_id' => functions::create_guid(),
                        'model_id' => $missionChain->guaranteedModelId,
                        'item_count' => 1,
                        'user_id' => session('user'),
                        'avatar_id' => session('avatar'),
                        'item_config' => "",
                        'item_icon_postfix' => null,
                    ]
                );
            }

            // $amf->rewardModelName = $missionChain->rewardModelName;
            // $amf->rewardXP = $missionChain->rewardXP;
            // $amf->rewardTokens = 1000;
            // $amf->rewardsCapCrossed = $missionChain->rewardsCapCrossed;
            // if ($missionChain->)
            if ($missionChain->guaranteedGold > 0 || $missionChain->guaranteedTokens > 0 || $missionChain->guaranteedXP > 0 || $missionChain->bonusTokens > 0 || $missionChain->bonusGold > 0)
            {
                // giver user gold
                if ($missionChain->guaranteedGold > 0 || $missionChain->bonusGold > 0)
                    Users::find(session('user'))->increment('goldBalance', $missionChain->guaranteedGold + $missionChain->bonusGold);

                if ($missionChain->guaranteedTokens > 0 || $missionChain->bonusTokens > 0)
                {
                    Users::where('id', session('user'))->increment('tokenBalance', $missionChain->guaranteedTokens + $missionChain->bonusTokens);
                    $amf->rewardTokens = (float) $missionChain->guaranteedTokens + $missionChain->bonusTokens;
                }
                
                // if ($user->serverIP != '127.0.0.1')
                //     $client->sendRequest('ds.updateBalance', array(session('user'), false, session('id'), session('avatar'))); 
                // else
                      //aid, xp, xpType
                // else // TODO if mission was completed under 3 minutes, reward 0 xp
                //     $client->sendRequest('ds.rewardMission', array(session('avatar'), 25, $missionChain->xpLevelTypeId));  //aid, xp, xpType
                // $client->sendRequest('ds.rewardMission', array(session('avatar'), functions::giveXP($missionChain->xpLevelTypeId, $missionChain->guaranteedXP),$missionChain->xpLevelTypeId));
            }
            // else
                // $client->sendRequest('ds.rewardMission', array(session('avatar'), functions::giveXP($missionChain->xpLevelTypeId, 1000),$missionChain->xpLevelTypeId));
            

            if (session('user') != $missionChain->creatorUserId)
            {
                $avatarMission->completed = Carbon::now();
                $avatarMission->activated = null;
                $avatarMission->save();
                $missionChain->totalPlays = $missionChain->plays + 1;
                $missionChain->plays = $missionChain->totalPlays;
                // do avg time calc
                $missionChain->weightedPlayTime = $missionChain->weightedPlayTime + $timeDiff / $missionChain->plays;
                $missionChain->save();
                // $missionChain->save();

            }
            else
            {
                $avatarMission->activated = null;
                // $avatarMission->completed = Carbon::now();
                $avatarMission->save();
            }
            

            //remove mission from avatar 
            // $task = missionTasks::find($taskId);
            // avatarMissions::where('missionId', $taskId)->where('avatar_id', session('avatar'))->delete();
            //figure out the next task to give
        }
        elseif (missionTasks::where('toTaskId', null) && $missionChain->followOnMissionId != null && $missionChain->followOnAutomatic)
        {
            $amf->chainComplete = true;
            $mission = missions::find($missionChain->followOnMissionId);
            $amf->unlockedMissionId = (float) $mission->firstTaskId;
            //remove mission from avatar 
            // $task = missionTasks::find($taskId);
            avatarMissions::where('missionId', $taskId)->where('avatar_id', session('avatar'))->delete();
            // $spaceId = session('space');
            $spaceId = onlineUsers::where('avatar_id', session('avatar'))->first()->space_id;
            // $amf->spaceId = $spaceId;
            // start follow on mission
            // $fmissionChain = missions::find($missionChain->followOnMissionId);
            // $this->activateMissionChain($timeconfig, $mission->followOnMissionId, $location_vector, $spaceId);
            avatarMissions::create([
                'avatar_id' => session('avatar'),
                'pickupSpaceId' => $spaceId,
                'missionId' => $mission->firstTaskId,
                'isTesting' => false,
                'activated' =>  Carbon::createFromTimestamp(strtotime(Carbon::now()))->format("Y-m-d H:i:s"),
                'pickupLocation' => $location_vector,
            ]);
            if (session('user') != $missionChain->creatorUserId)
            {
                $missionChain->plays += 1;
                $missionChain->totalPlays = $missionChain->plays;
                $missionChain->save();
            }
            // var_dump($this->activateMissionChain($timeconfig, $mission->followOnMissionId, $location_vector, $spaceId));
            //figure out the next task to give
        }
   
        else  
        {
            $amf->chainComplete = true;
            // update avatar mission to not active
            $avatarMission = avatarMissions::where('missionId',$missionChain->id)->where('avatar_id',functions::getAvatar('avatar_id'))->first();
            // $avatarMission->activated = false;
            $avatarMission->isTesting = false;
            $avatarMission->save();

        }
        $amf->success=true;
        return $amf;
    }
    function testMission($timeconfig, $id)
    {
        $amf = new stdClass();
        $aviMission = avatarMissions::find($id);
        if ($aviMission == null) 
        {
            $aviMission = avatarMissions::create([
                'avatar_id' =>session('avatar'),
                'missionId' => $id,
                'activated' =>  Carbon::createFromTimestamp(strtotime(Carbon::now()))->format("Y-m-d H:i:s"),
                'missionChainId' => missionTasks::find($id)->missionId,
                'pickupSpaceId' => onlineUsers::where('avatar_id', session('avatar'))->where('online',1)->first()->space_id,
                'isTesting' => true,
                'completed' => null,
            ]);
         
        }
        else
        {
            // $aviMission->activated = new Amfphp_Core_Amf_Types_Date(Carbon\Carbon::now()->valueOf());
            $aviMission->avatar_id = session('avatar');
            $aviMission->isTesting = true;
            $aviMission->missionId = $id;
            $aviMission->activated =  Carbon::createFromTimestamp(strtotime(Carbon::now()))->format("Y-m-d H:i:s");
            $aviMission->pickupSpaceId = onlineUsers::where('avatar_id', session('avatar'))->where('online',1)->first()->space_id;
            $aviMission->completed = null;
            $aviMission->save();
        }
        // $amf->space = onlineUsers::where('avatar_id', session('avatar'))->where('online',1)->first()->space_id;
        $amf->success=true;
        return $amf;
    }
    function viewMyActiveMissions()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";
        $avatar = new functions();

        if (avatarMissions::where('avatar_id', functions::getAvatar('avatar_id'))->count() > 0 ) 
        {
            
            // Result must be in this format
            $arr[] = MissionKeys::activeMissions;
            // avatarMission = avatarMissions::where('avatar_id', functions::getAvatar('avatar_id'))->get(); and completed time is null or date now is 7 days less than completed time
            $avatarMissions = avatarMissions::where('avatar_id', functions::getAvatar('avatar_id'))->where('completed', null)->where('activated', '!=', null)->get();
            if (avatarMissions::where('avatar_id', functions::getAvatar('avatar_id'))->where('completed', '<', Carbon::now()->subDays(7))->exists())
            {
                // $avatarMissions = avatarMissions::where('avatar_id', functions::getAvatar('avatar_id'))->where('completed', '<', Carbon::now()->subDays(7))->get();
                avatarMissions::where('avatar_id', functions::getAvatar('avatar_id'))->where('completed', '<', Carbon::now()->subDays(7))->update(['activated'=> null,'completed' => null]);
            
            }
            
            //  $avatarMissions = avatarMissions::where('avatar_id', functions::getAvatar('avatar_id'))->get();

            //  var_dump($avatarMissions);
            // if ($avatarMissions == null) 
            // {
            //     $avatarMissions = avatarMissions::where('avatar_id', functions::getUser('id'))->where('isTesting', 1)->get();
            // }
            // ($avatarMissions->count() > 0) ? $amf->count = $avatarMissions->count() : $avatarMission = avatarMissions::where('avatar_id', functions::getUser('id'))->where('isTesting', 1)->first();
            foreach ($avatarMissions as $avatarMission) 
            { // TODO
                $mission = missionTasks::find($avatarMission->missionId);
                $missionChain = missions::find($mission->missionId);
                $space = (avatarSpaces::find($avatarMission->pickupSpaceId) != null) ? avatarSpaces::find($avatarMission->pickupSpaceId) : null;

                // var_dump($avatarMission);
                // var_dump($missionChain);
                // $date = strtotime($avatarMission->activated);
                // $int = new Amfphp_Core_Amf_Types_Vector();
                // $int->type = Amfphp_Core_Amf_Types_Vector::;
                // $int->data = array(1,2,3);
                $arr[] = array 
                (
                    $missionChain->desc,
                    false, // expired logic
                    strval($avatarMission->pickupSpaceId), // $avatarMission->pickup_space_id,
                    $avatar->getAvatarByID($missionChain->creatorAvatarId, 'firstName'), // replace this with the avatar's name
                    ($space != null) ? $space->desc : null, // $space->desc,
                    $avatar->getAvatarByID($missionChain->creatorAvatarId, 'headPostfix'), // replace this with the avatar's head postfix
                    $missionChain->xpLevelTypeId,
                    ($space != null) ? $space->icon : null, // $space->icon_source,
                    ($space != null) ? $space->modelId : null, // $space->model_id,
                    $avatar->getAvatarByID($missionChain->creatorAvatarId, 'lastName'), // replace this with the avatar's name
                    $missionChain->activationGroup,
                    $mission->missionId,
                    $missionChain->creatorAvatarId,
                    $mission->desc,
                    strval($missionChain->firstMissionId),
                    $missionChain->creatorUserId,
                    $mission->completedDesc,
                    ($avatar->getAvatarByID($missionChain->creatorAvatarId, 'nameInstance') == 0) ? 1 : $avatar->getAvatarByID($missionChain->creatorAvatarId, 'nameInstance'), // $avatar->name_instance,
                    ($space != null) ? $space->spaceThumbnailSource : null, // $space->thumbnail_source,
                    ($space != null) ? $space->accessControl : null, // $space->access_control,
                    $missionChain->followOnMissionchainId,
                    $mission->script,
                    $avatar->getAvatarByID($missionChain->creatorAvatarId, 'headPostfix'), // $avatar->thumb_postfix,
                    ($space != null) ? $space->avatar_id : null, // $space->owner_id,
                    $avatar->getAvatarByID($missionChain->creatorAvatarId, 'snapshotPostfix'), // $avatar->snapshot_postfix,
                    $missionChain->panelStyle,
                    ($missionChain->followOnAutomatic) ? 'Y' : 'N',
                    strval($avatarMission->missionId),
                    -1,
                    $mission->title,
                    ($mission->expires) ? 'Y' : 'N',
                    //carbon time now format
                   ($avatarMission->activated != null) ? new Amfphp_Core_Amf_Types_Date( Carbon::createFromTimestamp(strtotime($avatarMission->activated))->valueOf()) : null,
            
                    // ($avatarMission->isTesting) ? 'Y' : 'N',
                    // (Carbon::createFromTimestamp($avatarMission->activated)->format('M j, Y, h:i:s A')),
                    // ($avatarMission->isTesting) ? 'Y' : 'N',
                    ($avatarMission->isTesting) ? 'Y' : 'N',
                    ($space != null) ? $space->name : null, // $avatarMission->pickup_space_name,
                    // ($avatarMission->activated) ? 'Y' : 'N',
                    //amf number type
                    1, // active
                    $avatarMission->pickupLocation, // $avatarMission->pickup_location_vector,
                    $missionChain->completedDesc,
                    $missionChain->title,
                );
            }
            $amf->recordSet = $arr;
        }
        else
            $amf->recordSet = array();
        $amf->startIndex = 0;
        $amf->maxLength = 0;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }
    
    function viewMissionChainInfo($timeconfig, $id)
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";

        $missions = missions::where('id', $id)->get();
            $avatar = new functions();

            // Result must be in this format
            $arr[] = MissionKeys::missionInfo;  
        

            foreach ($missions as $mission) 
            {
                $missionTask = missionTasks::find($mission->id);

             (avatarMissions::where('missionId', $mission->id)->count() != null) ? $avatarMission = avatarMissions::where('missionId', $mission->id)->first() : $avatarMission = null;
                $arr[] = array(
                    $mission->desc,
                    $mission->active, // active 1 or 0
                    $avatar->getAvatarByID($mission->creatorAvatarId,'firstName'), // change to avatar id of the mission creator
                    // $avatar->getAvatar('headPostfix'),
                    (float)$mission->xpLevelTypeId,
                    (float)$mission->minXPLevel,
                    (string)$mission->entryTokens,
                    $avatar->getAvatarByID($mission->creatorAvatarId,'lastName'),
                    $mission->activationGroup,
                    $avatar->getAvatarByID($mission->creatorAvatarId,'nameInstance'),
                    $mission->followOnMissionId,
                    // $avatar->getAvatar('headPostfix'),
                    // $avatar->getAvatar('snapshotPostfix'),
                    $mission->panelStyle,
                    ($mission->followOnAutomatic) ? 'Y' : 'N',
                    // null,
                    // // $mission->id, // avatarmission_mission_id
                    // null, // mission_time_left
                    // $missionTask->title, // mission_title
                    // $mission->id,
                    ($mission->expires) ? 'Y' : 'N',
                    $mission->completedDesc,
                    $mission->title,
                    strval($mission->id),
                    $mission->totalPlays,
                    $mission->rating,
                    $mission->votes,
                    $mission->weightedPlayTime, // average_time
                    1, // cooloff
                    -1, // missionchain_cooldown_time_override
                    (avatarMissions::where('missionChainId', $mission->id)->where('activated', '!=', null)->where('avatar_id', session('avatar'))->exists()) ? 1 : 0, // already_active
                    (avatarMissions::where('avatar_id', functions::getAvatar('avatar_id'))->where('missionChainId', $id)->where('completed', '>=', Carbon::now()->subDays(7))->exists() ) ? 0 : 1, // mission_owner
                    0, // too_many_active_missions
                    '', // avatar_head_postfix
                    '', // avatar_thumb_postfix
                    '', // avatar_snapshot_postfix

                );
            }
            $amf->recordSet = $arr;

        // }
        // else
        //     $amf->recordSet = array();
        // $amf->recordSet = array();
        $amf->startIndex = 0;
        $amf->maxLength = 0;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }
    
    function updateActiveMissions()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.mission.UpdateActiveMissionsResult";
        $amf->expiredMissions = array();
        $amf->success=true;
        $amf->newActiveMissions = array();
        return $amf;
    }
    function viewLoyaltyChallengeTasksOfWorld()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }

    public function activateMissionChain($timeconfig, $missionChainId, $vector, $spaceId)
    {        
        $amf = new stdClass();

        $date =  Carbon::now();
        $misson = missions::find($missionChainId);  
        $client = new SabreAMF_Client("https://".SITE_DOMAIN."/java/swds/gateway;jsessionid=".session('id')); // Set up the client object
        $task = missionTasks::find($misson->firstTaskId);  
        $avatarMission = avatarMissions::where('missionChainId', $missionChainId)->where('avatar_id', session('avatar'))->first();
        if ($avatarMission == null) 
        {
            //create new avatar mission
            $m = avatarMissions::create([
                'avatar_id' => session('avatar'),
                'pickupSpaceId' => $spaceId,
                'missionId' => $task->id,
                'missionChainId' => $missionChainId,
                'isTesting' => false,
                'activated' =>  Carbon::createFromTimestamp(strtotime($date))->format("Y-m-d H:i:s"),
                'pickupLocation' => $vector,
            ]);
            // $mission->update([
            //     'lastActivatedSpaceId' =>  $spaceId,
            // ]);
            missions::where('id', $missionChainId)->update([
                'lastActivatedSpaceId' =>  $spaceId,
            ]);
        }
        else
        {
            //update avatar mission
            $avatarMission->update([
                'pickupSpaceId' => $spaceId,
                'missionId' => $task->id,
                'missionChainId' => $missionChainId,
                'isTesting' => false,
                'activated' =>  Carbon::createFromTimestamp(strtotime($date))->format("Y-m-d H:i:s"),
                'pickupLocation' => $vector,
            ]);
            // $mission->update([
            //     'lastActivatedSpaceId' =>  $spaceId,
            // ]);
            missions::where('id', $missionChainId)->update([
                'lastActivatedSpaceId' =>  $spaceId,
            ]);
        }

        // RESPONSE MUST BE IN THIS FORMAT
        // $date->format('M j, Y, h:i:s A')
        // dont deduct tokens if it's their own mission
        if (missions::where('id', $missionChainId)->first()->creatorUserId != session('user'))
        {
            // figure out entryTokens, give tokens to mission creator user and deduct from current user
            $entryTokens = $misson->entryTokens;
            $missionUser = Users::find(missions::where('id', $missionChainId)->first()->creatorUserId);
            // deduct tokens from current user
            $user = Users::find(session('user'));
            if ($entryTokens > 0)
            {
                $user->tokenBalance = $user->tokenBalance - $entryTokens;
                $user->save();
                $client->sendRequest('ds.updateBalance', array(session('user'), false, session('id'), session('avatar'))); 

                // give tokens to mission creator
                $missionUser->tokenBalance = $missionUser->tokenBalance + $entryTokens;
                $missionUser->save();
                // if (onlineUsers::where('avatar_id', $mission->creatorAvatarId)->where('space_id', $spaceId)->where('online',true)->exists())
                //     $client->sendRequest('ds.updateBalance', array(null, false, null, $mission->creatorAvatarId));
            } 
        }
        
        $amf->success=true;
        return $amf;
        // $amf = new ServiceResult(true);
        // var_dump($amf->show());
        // return $amf->show();
    }

    function rateCompletedMissionChain($timeconfig, $missionChainId, $rating)
    {
        
        $amf = new stdClass();
        $missionChain = missions::find($missionChainId);
        //  take an average of all the ratings
        $missionChain->update([
            'rating' => ($missionChain->rating + $rating) / 2,
        ]);
    
      
        $amf->success=true;
        return $amf;
    }

    function addMissionKey($timeconfig, $missionId, $name, $visible, $ttl)
    {
        $amf = new stdClass();
        $visible = ($visible == 'true') ? 'Y' : 'N';
        // ttl is in seconds convert to timestamp
        avatarMissionKeys::create(['avatar_id' => session('avatar'), 'missionkey_key' => $name, 'missionkey_visible' => $visible, 'missionkey_expires' => date('Y-m-d H:i:s', time() + $ttl), 'missionkey_mission_id' => $missionId]);
        
        $amf->success=true;
        return $amf;
    }

    function removeMissionKey($timeconfig, $missionId, $name)
    {
        $amf = new stdClass();
        if (avatarMissionKeys::where('avatar_id', session('avatar'))->where('missionkey_key', $name)->where('missionkey_mission_id', $missionId)->exists()){
            avatarMissionKeys::where('avatar_id', session('avatar'))->where('missionkey_key', $name)->where('missionkey_mission_id', $missionId)->delete();
        }
        else
        {
            $amf->success=false;
        }
        $amf->success=true;
        return $amf;
    }
    function getGameTournament($timeconfig, $widgetId)
    {
        return new ServiceResult();
    }

    function enterTournament($timeconfig, $widgetId)
    {
        return new ServiceResult();
    }

    function giftTokens($timeconfig, $amount, $receiverAid, $receivingMsg )
    {
        // give tokens to the receiver
        $avi = Avatars::find(session('avatar'));
        $user = Users::find($avi->owner_id);
        if ($user->tokenBalance > $amount)
        {
            $user->tokenBalance = $user->tokenBalance - $amount;
            $user->save();
            $avi2 = Avatars::find($receiverAid);
            $user2 = Users::find($avi2->owner_id);
            $user2->tokenBalance = $user2->tokenBalance + $amount;
            $user2->save();
            // create a two messages for the sender and receiver
            $msg = new Messages();
            $msg->avatar_id = $receiverAid;
            $msg->told = $receiverAid;
            $msg->fromAvatarId = session('avatar');
            $msg->type = 'S';
            $msg->subject = 'You have received some tokens from '. functions::getAvatarByID(session('avatar'), 'firstName') . ' '. functions::getAvatarByID(session('avatar'), 'lastName');
            $msg->text = ($receivingMsg != '') ? functions::getAvatarByID(session('avatar'), 'firstName') . ' '. functions::getAvatarByID(session('avatar'), 'lastName') . ' gave '. $amount .' tokens to you. They said: ' . $receivingMsg : functions::getAvatarByID(session('avatar'), 'firstName') . ' '. functions::getAvatarByID(session('avatar'), 'lastName') . ' gave '. $amount .' tokens to you.';
            // generate random 20 character string
            $msg->ref = functions::generateRandomString(20);
            $msg->save();

            $msg2 = new messagesSent();
            $msg2->avatar_id = $receiverAid;
            $msg2->told = $receiverAid;
            $msg2->fromAvatarId = session('avatar');
            $msg2->type = 'S';
            $msg2->subject = 'You have received some tokens from '. functions::getAvatarByID(session('avatar'), 'firstName') . ' '. functions::getAvatarByID(session('avatar'), 'lastName');
            $msg2->text = ($receivingMsg != '') ? functions::getAvatarByID(session('avatar'), 'firstName') . ' '. functions::getAvatarByID(session('avatar'), 'lastName') . ' gave '. $amount .' tokens to you. They said: ' . $receivingMsg : functions::getAvatarByID(session('avatar'), 'firstName') . ' '. functions::getAvatarByID(session('avatar'), 'lastName') . ' gave '. $amount .' tokens to you.';
            // generate random 20 character string
            $msg2->ref = $msg->ref;
            $msg2->save();

        
            return new ServiceResult;
        }
        else return new ServiceResult(false, ErrorCodes::NOT_ENOUGH_CREDIT);
        //TODO
        // return new ServiceResult();

    }

    }

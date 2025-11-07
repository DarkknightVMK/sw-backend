<?php

namespace App\Http\Controllers\Auth;

use App\Models\Avatars;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\invites;
use App\Models\User;
use App\Models\sessions;
use App\Models\avatarItems;
use App\Models\spaceModels;
use App\Models\avatarSpaces;
use App\Models\xp_type;
use App\Models\avatar_xp;
use App\result\ErrorCodes;
use App\result\AvatarFunctions;
use App\Models\avatarWearing;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Http\Controllers\Utils;


class RegisterController extends Controller
{
    /**
     * Check if the name is available
     */
    public function name_available(Request $request)
      {

        // do not allow this list
        // [
//     "admin", "administrator", "moderator", "mod", "staff", "owner", "coowner", "founder", "support", "helper", "dev", "developer", "sysadmin", "eventmod", "gm", "gamemaster", "supermod", "management", "official", "team", "bot", "server", "system", "automod", "vip", "vipuser", "premium", "elite", "ambassador", "smallworlds", "smallworld", "smallworlds2", "sw2", "swteam", "smworlds", "smw", "smverse", "smallverse", "smallverse2", "smallworldsteam", "smallworldsadmin", "swadmin", "swmod", "smadmin", "smdev", "swdev", "swstaff", "swsupport", "minimania", "mini-mania", "minimania2", "minimaniaadmin", "minimod", "minimaniaevent", "minimaniateam", "minimaniaofficial", "minimondas", "minimanus", "minimon", "minimondo", "minimond", "smallw0rlds", "smallwurlds", "smalwurlds"
// ];
        $blockedNames = 
        [
            "admin", "administrator", "moderator", "mod", "staff", "owner", "coowner", "founder", "support", "helper", "dev", "developer", "sysadmin", "eventmod", "gm", "gamemaster", "supermod", "management", "official", "team", "bot", "server", "system", "automod", "vip", "vipuser", "premium", "elite", "ambassador", "smallworlds", "smallworld", "smallworlds2", "sw2", "swteam", "smworlds", "smw", "smverse", "smallverse", "smallverse2", "smallworldsteam", "smallworldsadmin", "swadmin", "swmod", "smadmin", "smdev", "swdev", "swstaff", "swsupport", "minimania", "mini-mania", "minimania2", "minimaniaadmin", "minimod", "minimaniaevent", "minimaniateam", "minimaniaofficial", "minimondas", "minimanus", "minimon", "minimondo", "minimond", "smallw0rlds", "smallwurlds", "smalwurlds"
        ];
        // block cursed words
        $cursedWords = 
        [
            "fuck", "shit", "bitch", "asshole", "dick", "cunt", "piss", 
    "bastard", "slut", "whore", "damn", "cock", "pussy", "fag", 
    "faggot", "retard", "twat", "cum", "nigger", "kike", "chink", 
    "gook", "wetback", "beaner", "cracker", "spic", "coon", "dyke", 
    "tranny", "rapist", "ped0", "ped0phile", "childp0rn", "cp", 
    "suicide", "kill", "murder", "terrorist", "bomb", "shoot", "stab", 
    "isis", "alqaeda", "nazi", "hitler", "gaschamber", "holocaust", 
    "meth", "cocaine", "heroin", "weed", "drugs", "overdose", "sex", 
    "sexy", "boobs", "vagina", "anus", "rape", "molest", "orgy", 
    "whorehouse", "prostitute", "escort", "cumdump", "anal", "blowjob", 
    "handjob", "deepthroat", "rimjob", "69", "420", "bdsm", "slave", 
    "slaveowner", "lynching", "gore", "bloodbath", "massacre", 
    "behead", "decapitate"
        ];

        if (Str::contains(strtolower($request['firstName']), $blockedNames) || Str::contains(strtolower($request['lastName']), $blockedNames))
        {
            return \response(
                [
                    'value' => false,
                    'message' => 'First & last name cannot be a blocked name!',
                    'isAvailable' => false
                ],
            200); 
        }
        if (Str::contains(strtolower($request['firstName']), $cursedWords) || Str::contains(strtolower($request['lastName']), $cursedWords))
        {
            return \response(
                [
                    'value' => false,
                    'message' => 'First & last name cannot be inappropriate!',
                    'isAvailable' => false
                ],
            200); 
        }
            
        // add some checks: first & last must be at least 3 characters long and only letters
        // Add a list of blocked last names unless user is admin
        if (Str::length($request['firstName']) < 3 || 
        Str::length($request['lastName']) < 3 ||
        !ctype_alpha($request['firstName']) || 
        !ctype_alpha($request['lastName']))
        {
            return \response(
                [
                    'value' => false,
                    'message' => 'First & last name must be greater than 3 characters and/or only contain letters!',
                    'isAvailable' => false
                ],
            200); 
        }
          function ordinal($number) {
              $ends = array('th','st','nd','rd','th','th','th','th','th','th');
              if ((($number % 100) >= 11) && (($number%100) <= 13))
                  return $number. 'th';
              else
                  return $number. $ends[$number % 10];
          }
          // ->where('gender', $request['gender'])
          if (Avatars::where('firstName', $request['firstName'])->where('lastName', $request['lastName'])->get() != '[]')
          {
              $nameInstance = count(Avatars::where('firstName', $request['firstName'])->where('lastName', $request['lastName'])->get()) + 1;
              return \response(
                  [
                      'nameInstance' => $nameInstance,
                      'fullName' => $request['firstName'] . " " . $request['lastName'] . " the ". ordinal($nameInstance),
                      'value' => true,
                      'isAvailable' => true],
                  200);

          }
          return \response(
              [
                  'nameInstance' => 1,
                  'fullName' => $request['firstName'] . " " . $request['lastName'],
                  'value' => true,
                  'isAvailable' => true,    
                ],
                  
              200);
      }
    /**
     *  Check if the filter is valid
     **/
    public function filter_validate(Request $request)
      { // TODO Add check in database
        // return $request[0];
        return \response(
            ['value' => true],
           200);
      }
      public function generateId($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }
    public function create_guid()
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

    public function create_avatar(Request $request)
    {
        // TODO: Reverify the rules before making db changes.
        function ordinal($number) {
            $ends = array('th','st','nd','rd','th','th','th','th','th','th');
            if ((($number % 100) >= 11) && (($number%100) <= 13))
                return $number. 'th';
            else
                return $number. $ends[$number % 10];
        }
        $fName = $request['firstName'];
        $lName = $request['lastName'];
        $gender = $request['gender'];
        // get current user permissions, check if 15 and if it is not check the amount of current avatars they have.
        if (!session()->has('user') && (Auth::user() == null))
        {
            // try to authenticate the user via swheader middleware
            return \response(
                [
                    'errorCode' => ErrorCodes::SESSION_EXPIRED,
                    // 'data' => $user,
                    'success' => false
                ], 200);
        }
        $user = (User::find(session('user'))) ? User::find(session('user')) : Auth::user();


        $avatar_url = \Config::get('custom.avatars_url');
        // check if user is admin 
        $count = Avatars::where('owner_id', $user->id)->count();
        if ($count >= 5 && !Utils::isAdmin($user))
        {
            return \response(
                [
                    'errorCode' => ErrorCodes::DO_NOT_HAVE_PERMISSION,
                    // 'data' => $user,
                    'success' => false
                ], 200);
        }
        

        // if (!session()->has('user'))
        // {
        //     return \response(
        //         [
        //             'errorCode' => ErrorCodes::SESSION_EXPIRED,
        //             // 'data' => $user,
        //             'success' => false
        //         ], 200);
        // }
        if ($fName != null && $lName != null && $gender != null)
        {
            $nameInstance = null;
            if (Avatars::where('firstName', $request['firstName'])->where('lastName', $request['lastName'])->get() != '[]')
          {
              $nameInstance = count(Avatars::where('firstName', $request['firstName'])->where('lastName', $request['lastName'])->get()) + 1;
          }
          // TODO do validation on the name and gender being M or F
          if (!$gender == "M" || !$gender == "F")
          {
            return \response(['errorCode' => ErrorCodes::INVALID_PARAM, 'success' => false], 200);
          }
        
         // create the avatar
         $avatar = Avatars::create([
             'avatar_id' => $this->generateId(32),
            'firstName' => $fName,
            'gender' => $gender,
            'lastName' => $lName,
            'fullName' => $fName . $lName,
            'nameInstance' => ($nameInstance == null) ? 1 : $nameInstance,
            'takePet' => false,
            'dateCreated' => date_create('now', null),
            'config' => ($gender == 'M') ? AvatarFunctions::menConfig : AvatarFunctions::womenConfig,
            'headPostfix' => ($gender == 'M') ? AvatarFunctions::menHead : AvatarFunctions::womenHead,
            'snapshotPostfix' => ($gender == 'M') ? AvatarFunctions::menHead : AvatarFunctions::womenHead,
            'thumbUrl' => ($gender == 'M') ? $avatar_url.'/'. AvatarFunctions::menHead .'_thumb.png': $avatar_url.'/'. AvatarFunctions::womenHead.'_thumb.png',
            'snapUrl' => ($gender == 'M') ? $avatar_url.'/'. AvatarFunctions::menHead .'_snap.png': $avatar_url.'/'. AvatarFunctions::womenHead.'_snap.png',
            'owner_id' => $user->id,
            'bonus' => '',
         ]);
         // create avatar XP
         $xpType = xp_type::all();
         foreach ($xpType as $xp)
         {
             if ($xp->id == 8)
             {
                 avatar_xp::create([
                     'avatar_id' => $avatar->avatar_id,
                     'level' => 499,
                     'xp' => 4716140,
                     'xpleveltype' => $xp->id,
                 ]);
             }
             else
             {
                avatar_xp::create([
                    'avatar_id' => $avatar->avatar_id,
                    'level' => 900,
                    'xp' => 0,
                    'xpleveltype' => $xp->id,
                ]);
            }
         }

        function createItems($item_id, $ic, $model_id, $aid, $user)
        {
         avatarItems::create
         (
             [
                 'item_id' => $item_id,
                 'model_id' => $model_id,
                 'item_count' => $ic,
                 'avatar_id' => $aid,
                 'user_id' => $user->id,
                 'item_config' => '',
             ]
         );
        }

        createItems($this->create_guid(), 1, 1830, $avatar->avatar_id, $user);
        createItems($this->create_guid(), 1, 1811, $avatar->avatar_id, $user);
        createItems($this->create_guid(), 1, 1793, $avatar->avatar_id, $user);

        $avatarItems = avatarItems::where('avatar_id', $avatar->avatar_id)->get();
        foreach ($avatarItems as $item)
        {
            avatarWearing::create(
                [
                    'avatar_id' => $avatar->avatar_id,
                    'item_id' => $item->item_id,
                ]
                );
        }
        // wear these items
        $space_name = $avatar->firstName. " " . $avatar->lastName."'s ". "House";
            
        // }
        $desc = spaceModels::where('model_id', 2638)->pluck('model_desc')->first();
        $space = avatarSpaces::create
        ([
            'avatar_id' => $avatar->avatar_id,
            'name' => $space_name,
            'modelId' => 2638,
            'config' => "",
            'desc' => $desc,
            'user_id' => $user->id,
        ]
        );
        $avatar->homeSpaceId = $space->id;
        $avatar->save();

        //  avatar_xp::create([
        //      'avatar_id' => $avatar->avatar_id,
        //      'xp' => 0,
        //  ]);
         return \response(
            [
                'id' => $avatar->avatar_id,
                'data' =>
                [
                    'configXML' => $avatar->config,
                    'firstName' => $avatar->firstName,
                    'lastName' => $avatar->lastName,
                    'nameInstance' => $avatar->nameInstance,
                    'gender' => $avatar->gender,
                    'id' => $avatar->avatar_id,
                    'snapUrl' => $avatar->snapUrl,
                    'thumbUrl' => $avatar->thumbUrl,
                    'name' => $avatar->firstName . $avatar->lastName,
                    'fullName' => (ordinal($avatar->nameInstance) == 1) ? $avatar->firstName . " " . $avatar->lastName : $avatar->firstName . " " . $avatar->lastName . " the ". ordinal($avatar->nameInstance),
                ],
                'success' => true
            ], 200);

        }
        else
        {
            return \response(
                [
                    'errorCode' => ErrorCodes::INVALID_PARAM,
                    // 'data' => $user,
                    'success' => false
                ], 200);
        }
                
        // Successful response:
         
        
        //TODO
        return \response(
            [
                'errorCode' => ErrorCodes::INTERNAL_ERROR,
                // 'data' => $user,
                'success' => false
            ], 200);
    }
    public function register(Request $request){
        // session()->flush();
        // session()->regenerate();

        $avatar_url = \Config::get('custom.avatars_url');

        $fields = $request->validate(
        [
            'avatar.firstName' => 'required|string',
            'avatar.gender' => 'required|string',
            'avatar.lastName' => 'required|string',
            'avatar.nameInstance' => 'optional|string',
            'user.firstName' => 'required|string',
            'user.lastName' => 'required|string',
            'user.sex' => 'required|string',
            'user.dobMonth' => 'required|string',
            'user.dobDate' => 'required|string',
            'user.dobYear' => 'required|string',
            'user.email' => 'required|string|unique:users,email',
            'user.password' => 'required|string',
            'user.question' => 'required|string',
            'user.answer' => 'required|string',
            'user.invite_code' => 'required|string|exists:invites,code',

        ]
        );

        if (invites::where('code', $fields['user']['invite_code'])->exists())
        {
            $invite = invites::where('code', $fields['user']['invite_code'])->first();
            session()->regenerate();
            session([
                'invite' => true,
                'code' => $fields['user']['invite_code'],
            ]);
        }
        else
        {
            return response('Invalid Invite Code', 401);
        }

        if (session('invite'))
        {

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
        $user = User::create(
            [
                'firstName' => $fields['user']['firstName'],
                'lastName' => $fields['user']['lastName'],
                'sex' => $fields['user']['sex'],
                'dob_month' => $fields['user']['dobMonth'],
                'dob_day' => $fields['user']['dobDate'],
                'dob_year' =>  $fields['user']['dobYear'],
                'email' => $fields['user']['email'],
                'password' => bcrypt($fields['user']['password']),
                'goldBalance' => 5000000,
                'tokenBalance' => 1000000,
                'citizenLevel' => 1,
                'security_question' => 1,
                'security_answer' => $fields['user']['answer'],
                'invite_code' => $fields['user']['invite_code'],
                'secondaryGroupIds' => '18', // Enable the header for all at first, this can be changed later on profile..

            ]
        );

        $avatar = Avatars::create(
            [
                'avatar_id' => $this->generateId(32),
                'firstName' => $fields['avatar']['firstName'],
                'gender' => $fields['avatar']['gender'],
                'lastName' => $fields['avatar']['lastName'],
                'fullName' => $fields['avatar']['firstName'] . $fields['avatar']['lastName'],
                'nameInstance' => isset($fields['avatar']['nameInstance']) ? $fields['avatar']['nameInstance'] : 1,
                'takePet' => false,
                'dateCreated' => date_create('now', null),
                'config' => ($fields['avatar']['gender'] == 'M') ? AvatarFunctions::menConfig : AvatarFunctions::womenConfig,
                'headPostfix' => ($fields['avatar']['gender'] == 'M') ? AvatarFunctions::menHead : AvatarFunctions::womenHead,
                'snapshotPostfix' => ($fields['avatar']['gender'] == 'M') ? AvatarFunctions::menHead : AvatarFunctions::womenHead,
                'thumbUrl' => ($fields['avatar']['gender'] == 'M') ? $avatar_url.'/'. AvatarFunctions::menHead .'_thumb.png': $avatar_url.'/'. AvatarFunctions::womenHead.'_thumb.png',
                'snapUrl' => ($fields['avatar']['gender'] == 'M') ? $avatar_url.'/'. AvatarFunctions::menHead .'_snap.png': $avatar_url.'/'. AvatarFunctions::womenHead.'_snap.png',
                'owner_id' => $user->id,
                'bonus' => '',

            ]
        );
         User::where('id', $user->id)->update(['choosenAvatar' => $avatar->avatar_id, 'defaultAvatar' => $avatar->avatar_id, 'secondaryGroupIds' => '18']);
        // $user->choosenAvatar = $avatar->avatar_id;
        // $user->defaultAvatar = $avatar->avatar_id;
        // $user->save();

        $xpType = xp_type::all();
         foreach ($xpType as $xp)
         {
             if ($xp->id == 8)
             {
                 avatar_xp::create([
                     'avatar_id' => $avatar->avatar_id,
                     'level' => 1,
                     'xp' => 0,
                     'xpleveltype' => $xp->id,
                 ]);
             }
             else
             {
                avatar_xp::create([
                    'avatar_id' => $avatar->avatar_id,
                    'level' => 1,
                    'xp' => 0,
                    'xpleveltype' => $xp->id,
                ]);
            }
         }


        function createItems($item_id, $ic, $model_id, $uid, $aid)
        {
         avatarItems::create(
             [
                 'item_id' => $item_id,
                 'model_id' => $model_id,
                 'item_count' => $ic,
                 'avatar_id' => $aid,
                 'user_id' => $uid,
                 'item_config' => '',
             ]
         );
        }

        createItems(create_guid(), 1, 1830, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 1811, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 1793, $user->id, $avatar->avatar_id);
         $avatarItems = avatarItems::where('avatar_id', $avatar->avatar_id)->get();
        foreach ($avatarItems as $item)
        {
            avatarWearing::create(
                [
                    'avatar_id' => $avatar->avatar_id,
                    'item_id' => $item->item_id,
                ]
                );
        }

        createItems(create_guid(), 1, 3924, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2091, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2092, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2093, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2094, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2095, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2096, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2097, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2098, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2099, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2100, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2101, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2102, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2103, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 2104, $user->id, $avatar->avatar_id);
        //Speech Bubbles
        createItems(create_guid(), 1, 5604, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 5605, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 5606, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 5607, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 5608, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 5609, $user->id, $avatar->avatar_id);
        createItems(create_guid(), 1, 5610, $user->id, $avatar->avatar_id);


//      $avatar_id = User::find('id');
//      $avatar_id->save();


//          Avatar::where('avatar_id')->first();

$credentials = $request->only(['email' => $user->email, 'password' => $user->password]);
        $au = false;
        // if(Auth::loginUsingId($user->id, true)){
        //     $au = true;
        // }
        // Default Homespace
        $space_name = $avatar->firstName . " " .$avatar->lastName."'s ". "House";
            
        // }
        $desc = spaceModels::where('model_id', 2638)->pluck('model_desc')->first();
        $space = avatarSpaces::create
        ([
            'avatar_id' => $avatar->avatar_id,
            'name' => $space_name,
            'modelId' => 2638,
            'config' => "",
            'desc' => $desc,
            'user_id' => $user->id,

        ]
        );
        // Home space ? true else false... update value
        $homeID = DB::getPdo()->lastInsertId();

        Avatars::where('avatar_id', $avatar->avatar_id)->update(['homeSpaceId' => $homeID]);
        // end Default Homespace
        
        $unique_session = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/strlen($x)) )),1,32);  // generate a random 32 character string
        
       

        $session = sessions::where('user_id', $user->id)->exists();
        if (!$session)
        {
            sessions::create(
                [
                    'SWSID' => $unique_session,
                    'user_id' => $user->id,
                    'ip_address' => $this->getUserIpAddr(),
                    'user_agent' => $request->userAgent(),
                    'expires_at' => now()->addMinutes(120)
                ]
            );
        }
        else
        {
            sessions::where('user_id', $user->id)->update(['SWSID' => $unique_session]);
        }
        session()->regenerate();
        session([
            'id' => $unique_session,
            'user' => $user->id,
            'avatar' => $avatar->avatar_id,
            'email' => $user->email,
            'password' =>  $user->password,
        ]);


        $token = $user->createToken('myapptoken')->plainTextToken;
        $response = [
            'id' => $unique_session,
            'user' => $user,
            'avatar' => $avatar,
            'token' => $token,
            'auth' => $au,
        ];
        return response($response, 201);
    }
    else
        return abort(401);
    }

    public function getUserIpAddr(){
        $ipaddress = '';
        
         //get the ip address from the header
          if (getenv('HTTP_CLIENT_IP'))
              $ipaddress = getenv('HTTP_CLIENT_IP');
          else if(getenv('HTTP_X_FORWARDED_FOR'))
              $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
          else if(getenv('HTTP_X_FORWARDED'))
              $ipaddress = getenv('HTTP_X_FORWARDED');
          else if(getenv('HTTP_FORWARDED_FOR'))
              $ipaddress = getenv('HTTP_FORWARDED_FOR');
          else if(getenv('HTTP_FORWARDED'))
              $ipaddress = getenv('HTTP_FORWARDED');
          else if(getenv('REMOTE_ADDR'))
              $ipaddress = getenv('REMOTE_ADDR');
          else
              $ipaddress = 'UNKNOWN';

              if (strpos($ipaddress, ',') !== false) {
                $ipaddress = explode(',', $ipaddress);
                $ipaddress = $ipaddress[0];
            }

        return $ipaddress;
      }
}

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

class AuthController extends Controller
{
  public function index()
  {
    // auth()->user()->auth()
    // if (Auth::user() == null)
    // {
    //   return User::id();
    // }
    // else {
    return \response(
        [
            'errorCode' => 2,
            'data' => null,
            'success' => false
        ], 200);
      }

      
    //function to generate a random string by length
    

  public function emailAvail($email)
  {
    if (User::where('email', $email)->get() != '[]')
    {
      return \response(
          [
              'errorCode' => 2,
              'data' => null,
              'success' => false
          ], 200);
    }
    else{
    return \response(
        [
            'value' => true,
            'success' => true
        ], 200);
      }
  }
  public function ip(Request $request)
  {
    if(filter_var($request->serverIP, FILTER_VALIDATE_IP) !== false)
    {
        // is an ip
        User::where('id', $request->id)->update(['serverIP' => $request->serverIP]);
        return \response(
            [
                'serverIP' => $request->serverIP,
                'data' => null,
                'success' => true
            ], 200);

    } else {
        // is not an ip
        return \response(
            [
                'errorCode' => 2,
                'data' => null,
                'success' => false
            ], 200);
    }
  }
  public function cp(Request $request)
  {
    if(filter_var($request->contentPath, FILTER_VALIDATE_URL) !== false)
    {
        // is an url
        User::where('id', $request->id)->update(['contentPath' => $request->contentPath]);
        return \response(
            [
                'contentPath' => $request->contentPath,
                'data' => null,
                'success' => true
            ], 200);

    } else {
        // is not an ip
        return \response(
            [
                'errorCode' => 2,
                'data' => null,
                'success' => false
            ], 200);
    }
  }

  public function justRegistered(Request $request)
  {
    if (session('email') != null && session('password') !=null)
    {
        return response()->json(['success' => true, 'email' => session('email'), 'pass' => session('password') ]);
    }
    else
        return response()->json(['success' => false]);

  }
    

    public function check(Request $request)
    {

        if (Auth::check())
        {
            $session = sessions::where('user_id', session('user'))->pluck('SWSID')->first();
            if ( $session != session('id'))
            {
                // auth()->user()->tokens()->delete();
                Auth::logout();
                session()->flush();
            }
            return response('valid', 200);

        }
        else if (session('invite'))
        {
            return response('valid', 200);
        }
        // return view('access');
        return abort(401);
        // return response()->view('errors_401', 401);

        // return redirect()->route('invite');
    }

    public function admin_check(Request $request)
    {
        if (Auth::check())
        {
            if (User::where('id', Auth::user()->id)->pluck('primaryGroupId')->first() == 1)
            {
                return response('valid', 200);
            }
            else
            {
                return abort(403);
            }
            // return response('valid', 200);
        }

        // return view('access');
        return abort(401);

    }

    public function sso(Request $request)
    {
        //validate fields
        if(!$request->swsid)
        {
            return response('SWSID not found', 401);
        }
        // if (!$request->redirect)
        // {
        //     return response('Redirect URI not found', 400);
        // }

        $SWSID = $request->swsid;
        $uri = $request->redirect;
        $session = sessions::where('SWSID', $SWSID)->get()->first();
        if ($session)
        {
            $user = User::where('id', $session->user_id)->get()->first();
            Auth::loginUsingId($session->user_id);
            session()->regenerate();
            session([
                'id' => $SWSID,
                'user' => $session->user_id,
                'avatar' => $session->user_id
            ]);
                $response = [
                'user' => $user,
                'session' => session()->all()
                // 'token' => $token,
            ];
            return response($response, 200);
        }
        else
        {
            return response('Invalid SWSID', 401);
        }
        // return view('access');
        return abort(401);
    }

    public function session(Request $request)
    {
        $SWSID = session('id');
        $website = 'https://sw.seconddomain.com/api/';

        return response(
                [
                    'swsid' => $SWSID,
                    'website' => $website,
                ], 200);
    }

    public function invite(Request $request)
    {
        $email = $request->remail;
        $code = $request->rcode;
        $user = User::where('invite_code', $request->rcode)->exists();

        if ($code != null && !$user)
        {
            if (invites::where('code', $code)->exists())
            {
                $invite = invites::where('code', $code)->first();
                // $invite->update(['code' => null]);
                // session()->regenerate();
                session([
                    'invite' => true,
                    'code' => $code,
                ]);
                return response('valid', 200);
            }
            else
            {
                return response('invalid', 401);
            }
        }
        else
         return response('invalid', 401);
    }

    function inviteGenerate(Request $request)
    {
        if (Auth::check())
        {
            $user = User::where('id', Auth::user()->id)->first();
            $secondaryGroups = explode(',', $user->secondaryGroupIds);
            if ($user->primaryGroupId == 1 || in_array(21, $secondaryGroups))
            {
                $code = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(6/strlen($x)) )),1,6);
                $email = $request->email;

                if (invites::where('email', $email)->exists() && filter_var($email, FILTER_VALIDATE_EMAIL))
                {
                    $invite = invites::where('email', $email)->first();
                    $invite->update(['code' => $code]);
                    return response('Updated ' . $email . " ". $code, 200);
                }
                elseif (filter_var($email, FILTER_VALIDATE_EMAIL))
                {
                    invites::create(
                        [
                            'email' => $email,
                            'code' => $code,
                            'invitee' => session('user'),
                        ]
                    );
                    return response('valid: ' . $code, 200);
                }
                return response('valid', 403);
            }
        }
        return response('invalid', 401);
    }

    public function loginWithToken(Request $request)
    {
        $token = $request->token;
        $session = sessions::where('SWSID', $token)->get()->first();
        if ($session)
        {
            $user = User::where('id', $session->user_id)->get()->first();
            Auth::loginUsingId($session->user_id);
            session()->regenerate();
            session([
                'id' => $token,
                'user' => $session->user_id,
                'avatar' => $session->user_id
            ]);
            $response = [
                'user' => $user,
                'api' => $token,
                'SWSID' => $session,
                'success' => true
                // 'token' => $token,
            ];
            return response($response, 200);
        }
        else
        {
            return response('Invalid SWSID', 401);
        }
        // return view('access');
        return abort(401);
    }


    public function login(Request $request){
        session()->regenerate();

        $cookie_url = \Config::get('custom.cookie_url');
        // if $token passed
        
        $fields = $request->validate(
            [
                'email' => 'required|string',
                'password' => 'required|string',
                'rememberMe' => 'boolean'
            ]
        );
//        $user = User::create(
//            [
//                'name' => $fields['name'],
//                'email' => $fields['email'],
//                'password' => bcrypt($fields['password'])
//            ]
//        );
        //check email
        $user = User::where('email', $fields ['email'])->first();
        if (!$user)
        {
            return \response(
                [
                    'errorCode' => 301,
                    'data' => null,
                    'success' => false
                ], 200);
        }
        $credentials = $request->only('email', 'password');
        $au = false;
        if(Auth::attempt($credentials))
        {
            $au = true;
        }
        //Check pw
        else if(!Hash::check($fields['password'], $user->password))
        {
            return \response(
                [
                    'errorCode' => 303,
                    'data' => null,
                    'success' => false
                ], 200);

        }
        $unique_session = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/strlen($x)) )),1,32);
        $token = $user->createToken('apiKey')->plainTextToken;
        $session = sessions::where('user_id', $user->id)->exists();
        function getUserIpAddr(){
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
        if (!$session)
        {
            sessions::create(
                [
                    'SWSID' => $unique_session,
                    'user_id' => $user->id,
                    'ip_address' => getUserIpAddr(),
                    'user_agent' => $request->userAgent(),
                    //TODO get this data from env  session
                    'expires_at' => now()->addDays(30)
                ]
                );
        }
        else
        {
            //a session exists delete all then create a new one..
            $session = sessions::where('user_id', $user->id)->get();
            foreach ($session as $s)
            {
                $s->delete();
            }

            sessions::create(
                [
                    'SWSID' => $unique_session,
                    'user_id' => $user->id,
                    'ip_address' => getUserIpAddr(),
                    'user_agent' => $request->userAgent(),
                    'expires_at' => now()->addDays(30)
                ]
                );
        }

        // $request->session()->put('id', $unique_session);
        // \Session::put('id', $unique_session);
        session([
            'id' => $unique_session,
            'user' => $user->id,
            'avatar' => $user->choosenAvatar
        ]);
        // store just the SWSID and the expires_at in json_session
        $json_session = sessions::where('user_id', $user->id)->first();
        // $json_session = json_encode(['SWSID' => $json_session, 'expires_at' => sessions::where('user_id', $user->id)->pluck('expires_at')->first()]);

        $response = [
            'user' => $user,
            'defaultAvatar' => Avatars::where('avatar_id', $user->id)->first(),
            'SWSID' => ($json_session),
            'api' => $token,
            'success' => true
        ];

        return response($response, 200);
    }
    public function logout(Request $request)
    {
        $userID = Auth::user()->id;
        $session = sessions::where('user_id', $userID)->exists();
        if ($session)
        {

            $session = sessions::where('user_id', $userID)->get();
            foreach ($session as $s)
            {
                $s->delete();
            }
            sessions::where('user_id', $userID)->delete();
        }
                // auth()->user()->tokens()->delete();
        session()->flush();

        // $_COOKIE['api_token'] = "";
        // session_regenerate_id();
        // return response();
        return response('200', 200);

        // return ['message' => 'Logged out'];
    }

}

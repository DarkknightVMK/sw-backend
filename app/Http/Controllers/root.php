<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\invites;
use App\Models\Users;
class root extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function web_invite()
    {

        return view('invite');
    }

    public function index(Request $request)
    {
        $session_data = session()->all();
//        $user_id = Auth::user()->id;
$u_agent = $_SERVER['HTTP_USER_AGENT'];
$valid_agent = false;
$update = false;
if(preg_match('/YOUR_CUSTOM_AGENT-Mac-x64/i',$u_agent))
  $valid_agent = true;
elseif(preg_match('/YOUR_CUSTOM_AGENT-Windows-x64/i',$u_agent))
  $valid_agent = true;
elseif(preg_match('/YOUR_CUSTOM_AGENT-SERVER/i', $u_agent))
  $valid_agent = true;

  if(preg_match('/YOUR_CUSTOM_AGENT-Mac-x64/i',$u_agent))
    $update = true;
elseif(preg_match('/YOUR_CUSTOM_AGENT-Windows-x64/i',$u_agent))
    $update = true;

        // var_dump( $session_data);
        // $value = session('id');
        // var_dump($value);
        // var_dump(Auth::check());
        if (!Auth::check())
        {
            if ($valid_agent)
                return view('index');
            else if ($update){
                session ([
                    'update' => true,
                ]);
                return view('download');
            }
            return abort(401);
        }
        else
            return view('profile');
    }
    public function err401()
    {
        return abort(401);
    }
    public function err403()
    {
        return abort(403);
    }

    public function vue()
    {
        return view('vue');
    }
    public function vueLogin()
    {
        return view('vueLogin');
    }
    
    public function vueTest()
    {
        return view('vueTest');
    }

    public function conf(Request $request)
    {
        return view('configString');
    }
    
    public function index2(Request $request)
    {
        $session_data = session()->all();

        if (!Auth::check())
        {
            return view('swindex');
        }
        else
            return view('profile2');
    }

    public function profile(Request $request)
    {
        if (Auth::check())
        {
            return view('profile');
        }
        return view('swindex');

    }

    public function avatarCreate(Request $request)
    {
        if (Auth::check())
        {
            return view('createavatar');
        }
        return view('swindex');

    }

    public function itemsworn(Request $request)
    {
        return view('itemsworn');
    }

    public function dash(Request $request)
    {
        return view('dash');
    }
    public function home_arcade(Request $request)
    {
        return view('includes.game_arcade');
    }
    public function home_cp(Request $request)
    {
        return view('includes.cp');
    }
    public function home_loyalty(Request $request)
    {
        return view('includes.loyalty');
    }
 
    public function home_places(Request $request)
    {
        return view('includes.places');
    }
    public function home_xp(Request $request)
    {
        return view('includes.xp');
    }
    
    public function profile2(Request $request)
    {
        return view('profile2');
    }

    public function invite(Request $request)
    {
        $code = $request->code;
        $email = '';
        if (session('user'))
        {
            return view('profile');
        }
        if ($code != null)
        {
            $invite = invites::where('code', $code)->first();
            if ($invite != null)
            {
                $email = $invite->email;
            }        
            return view('access', compact('code', 'email'));
        }
        return view('access', compact('code', 'email'));

    }

    public function register()
    {
        if (Auth::check())
        {
            return view('register');
        }
        else if (session('invite'))
        {
            return view('register');
        }
        
        return abort(403);
    }
    public function swmod()
    {
        return view('swmod');
    }
    public function swadmin()
    {
        return view('swadmin');
    }
    public function setsso()
    {
        \Cookie::queue('sso', 'true', 600, null, null, false, false);
        return redirect(route('home'));
    }
    public function addInvite()
    {
        $user = Users::where('id', Auth::user()->id)->first();
        $secondaryGroups = explode(',', $user->secondaryGroupIds);
        if (Auth::check() && $user->primaryGroupId == 1 || Auth::check() && in_array(22, $secondaryGroups))
        {
            return view('addInvites');
        }
        return abort(403);
    }

    public function upload()
    {
        $user = Users::where('id', Auth::user()->id)->first();
        $secondaryGroups = explode(',', $user->secondaryGroupIds);

        if (Auth::check() && $user->primaryGroupId == 1 || in_array(17, $secondaryGroups))
        {
            return view('upload');
        }
        return abort(403);
    }

    public function aitembymodel()
    {
        $user = Users::where('id', Auth::user()->id)->first();
        $secondaryGroups = explode(',', $user->secondaryGroupIds);
        if (Auth::check() && $user->primaryGroupId == 1 || Auth::check() && in_array(17, $secondaryGroups))
        {
            return view('additemmodel');
        }
        return abort(403);
    }

    public function spaceModels()
    {
        $user = Users::where('id', Auth::user()->id)->first();
        $secondaryGroups = explode(',', $user->secondaryGroupIds);
        if (Auth::check() && $user->primaryGroupId == 1 || in_array(17, $secondaryGroups))
        {
            return view('addspacemodels');
        }
        return abort(403);
    }

    public function fixitems(Request $request)
    {
        $swsid = $request->header('SWSID');
        $file = $request->file;

        $user = Users::where('id', Auth::user()->id)->first();
        $secondaryGroups = explode(',', $user->secondaryGroupIds);

        if (Auth::check() && $user->primaryGroupId == 1 || in_array(17, $secondaryGroups))
        {
            if ($file !=null)
                return view('editfiles', compact('file'), compact('swsid'));
            return view('fixitems', compact('swsid'));
        }
        return abort(403);
    }
    public function allitems()
    {
        if (Auth::check())
        {
            return view('allitems');
        }
        return abort(401);
    }
    public function missitems()
    {
        if (Auth::check())
        {
            return view('missitems');
        }
        return abort(401);
    }
    public function swm()
    {
        if (Auth::check())
        {
            return view('swm');
        }
        else 
            return abort(401);
    }

    public function s2w()
    {
        return view('s2w');
    }
    public function plant(Request $request)
    {
        return view('plants');
    }
    public function petPanel(Request $request)
    {
        return view('petpanel');
    }

    public function makeInvites(Request $request)
    {
        //$invites = invites::all();
        $invites = invites::where('code', '')->get();
        $count = count($invites);
        
        // make 6 random strings and numbers function to make a random string
        $rand_string = function($length = 6) {
            return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
        };

        for ($i = 0; $i < $count; $i++)
        {
            $invites[$i]->code = $rand_string();
            $invites[$i]->save();
        }

    }

}

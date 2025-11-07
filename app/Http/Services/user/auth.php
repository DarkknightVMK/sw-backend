<?php

use App\Models\sessions;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use App\Models\permissionCategories;
use App\Models\userPermissions;
use App\Models\userGroups;
// use Illuminate\Support\Facades\Auth;

class auth
{
    var $_explicitType = "user.auth";
    public function loginSMI ($timeconfig, $email, $password, $tfa)
    {
        // session()->regenerate();        
        $amf = new stdClass();

        if (!session('user'))
        {
        $user = Users::where('email', $email)->first();
        // $credentials = $request->only('email', 'password');
        // $au = false;
        // if(Auth::attempt([$email, $password]))
        // {
        //     $au = true;
        // }
        if (!$user) {
            $amf->code = 301;
            $amf->success = false;
            return $amf;
        }
        else if(!Hash::check($password, $user->password))
        {
            $amf->code = 308;
            $amf->success = false;
            return $amf;
        }
//        $arr = array();
//        $arr[] = array (
//            'userId' => $user->id,
//            'firstName' => $user->firstName,
//            'lastName' => $user->lastName
//        );
        $unique_session = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/strlen($x)) )),1,32);
        $token = $user->createToken('myapptoken')->plainTextToken;
        $session = sessions::where('user_id', $user->id)->exists();
        if (!$session)
        {
            sessions::create(
                [
                    'SWSID' => $unique_session,
                    'user_id' => $user->id
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
                    'user_id' => $user->id
                ]
                );
        }
//        request()->cookie('api_token', $token, 900, '/', '.deploy-sw.com',true, false);
        // setcookie('api_token', $token, time() + (86400 * 30), "/");
//        $_SESSION["userId"] = $user->id;
//        $_SESSION["avatarId"] = $user->id;
//        $_SESSION["avatarOwnerId"] = $user->id;
        session([
            'id' => $unique_session,
            'user' => $user->id,
            'avatar' => $user->id
        ]);

       $amf->userId = $user->id;
        $amf->firstName = $user->firstName;
        $amf->lastName = $user->lastName;
        $sgroup = userGroups::where('type', "A")->get();
        //primary group & secondary groups
        $primaryGroup = $user->primaryGroupId;
        $secondaryGroups = $user->secondaryGroupIds;
//        if (str_contains($user->groups,"1"))
//        {
//            $group = userGroups::where('id', 1)->pluck('permissionId');
//            $group2 = str_replace('["', '', $group);
//            $group3 = str_replace('"]', '', $group2);
//            $pid = explode(', ', strval($group3));
//
//            foreach ($pid as $perm) {
//                $arr[$perm] = true;
//            }
//            $amf->permissions = $arr;

            if ($secondaryGroups == null)
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
//            $_SESSION["permissions"] = $arr;
//            $_SESSION["currentUserFName"] = $user->firstName;



//        }

        }
        else
        {
            $user = Users::where('id', session('user'))->first();
            $primaryGroup = $user->primaryGroupId;
            $secondaryGroups = $user->secondaryGroupIds;
            $amf->userId = strval($user->id);
            $amf->firstName = $user->firstName;
            $amf->lastName = $user->lastName;

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
        }
        $amf->success = true;
        return $amf;
    }

    function getPermissions($user)
    {
        
    }

}

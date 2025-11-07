<?php

namespace App\Http\Controllers;
use App\Models\invites;
use App\Models\User;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class InvitesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function downloads()
    {
        $invite = invites::where('code', session('code'))->first();
        // var_dump(session('invite'));
        if ($invite->code != null || session('update'))
            return view('download');
        return abort(403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function make(Request $request)
    {
        $user = Users::where('id', Auth::user()->id)->first();
            $secondaryGroups = explode(',', $user->secondaryGroupIds);
        if (Auth::check() &&  $user->primaryGroupId == 1 || Auth::check() && in_array(22, $secondaryGroups))
        {
            $code = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(6/strlen($x)) )),1,6);
            $email = $request->email;

            if (invites::where('email', $email)->exists() && filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $invite = invites::where('email', $email)->first();
                // $invite->update(['code' => $code]);
                // return response('Updated ' . $email . " ". $code, 200);
                //env variable APP_DOMAIN
                return response()->json(['success' => true, 'msg' => "That email already exists, the code is : https://".env('APP_DOMAIN')."/invite/".$invite->code]);
            }
            elseif (filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                invites::create(
                    [
                        'email' => $email,
                        'code' => $code,
                        'invitee' => Auth::user()->id,
                    ]
                );
                return response()->json(['success' => true, 'msg' => "The invite code to share is: https://".env('APP_DOMAIN')."/invite/" . $code]);
            }
            return response('valid', 403);
        }
    }

    public function webCheckInvite(Request $request)
    {
         $invite = invites::where('code', $request->invite)->get()->first();
         $user = User::where('invite_code', $request->invite)->exists();

        if ($request->invite != null && $invite != null && !$user)
        {
            session()->regenerate();
            $unique_session = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/strlen($x)) )),1,32);

            session(
            [
                'invite' => true,
                'email' => $invite->email,
                'SWID' => $unique_session,
            ]);

            return redirect()->route('register');

        }
        else
        {
            //return 401
            // return redirect()->route('access');
            return response()->json(['success' => false]);
        }   
    }
    public function checkInvite(Request $request)
    {
        $invite = invites::where('code', $request->code)->get()->first();
        $user = User::where('invite_code', $request->code)->exists();

        // if ($invite)
        // {
        //     return response()->json(['success' => true, 'invite' => $invite]);
        // }
        // else
        // {
        //     return response()->json(['success' => false]);
        // }
        if ($request->code != null && $invite != null || $invite->code == 'try')
        {
            session()->regenerate();
            $unique_session = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/strlen($x)) )),1,32);

        session([
                'invite' => true,
                'email' => $invite->email,
                
            ]);
            //return 200
            return response()->json(['success' => true])->cookie('SWSID', $unique_session, null);

            // return redirect()->route('register');

        }
        else
        {
            //return 401
            // return redirect()->route('access');
            return response()->json(['success' => false, 'message' => 'Invalid invite code, please try again.']);
        }   
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

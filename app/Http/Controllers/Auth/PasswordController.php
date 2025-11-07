<?php

namespace App\Http\Controllers\Auth;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Str;

//mail
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;


class PasswordController extends Controller
{
    /**
     * forgotPassword
     */
    
    public function forgotPassword(Request $request)
    {

        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        // create password reset code
        $reset_code = Str::random(64);

        // if a valid reset code exists in db return true, updated_at or created_at must be valid (within 30 mins of creation)
        $existing_reset = PasswordReset::where('email', $request->email)->first();
        // is the token still valid?
        

        if ($user) {            
            if ($existing_reset) {
                $created_at = strtotime($existing_reset->created_at);
                $now = strtotime(date('Y-m-d H:i:s'));
                $diff = $now - $created_at;
                if ($diff < 1800) {
                    return response()->json(['message' => 'A current password reset code is still valid. Check your email.'], 200);
                }
            } else {
                PasswordReset::updateOrCreate(
                    ['email' => $user->email],
                    ['token' => $reset_code]
                );
                Mail::to($user->email)->send(new PasswordResetMail($reset_code));
            }
        }

        return response()->json(['message' => 'If email exists, a reset link has been sent.'], 200);
    }

    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string'
        ]);

        $reset = PasswordReset::where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if ($reset) {
            return response()->json(['message' => 'Reset code is valid.'], 200);
        }

        return response()->json(['message' => 'Invalid reset code.'], 400);
    }
    


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
            'password' => 'required|string|min:8'
        ]);

        $reset = PasswordReset::where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if ($reset) {
            $user = User::where('email', $request->email)->first();
            $user->password = bcrypt($request->password);
            $user->save();

            PasswordReset::where('email', $request->email)->delete();
            return response()->json(['message' => 'Password reset successful.'], 200);
        }

        return response()->json(['message' => 'Invalid reset code.'], 400);

    }
}
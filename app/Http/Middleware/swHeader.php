<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\sessions;
use Illuminate\Support\Facades\Auth;


class swHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // verify the session
        $session = sessions::where('SWSID', $request->header('SWSID'))->first();
        if (!$session) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // authenticate user 
        //set auth user
        Auth::loginUsingId($session->user_id);

        return $next($request); 
    }
}

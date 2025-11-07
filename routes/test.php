<?php

use App\Http\Controllers\SpaceController;
use App\Http\Controllers\root;
use App\Http\Controllers\AmfphpController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Models\User;
use App\Models\sessions;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Route::middleware('auth:sanctum')->get('/space/{id}', [SpaceController::class, 'index']);
// Route::get('/space/{id}', function () {
//   Redirect::to('/space/{id}' . '\/', 301);});

Route::get('/space/{id}/',[SpaceController::class, 'test'])->middleware("slashes:add");
Route::get('/shops/{id}/',[SpaceController::class, 'index'])->middleware("slashes:add");
Auth::routes();

Route::get('/home/{id}', [SpaceController::class, 'index'])->middleware("slashes:add");

Route::get('/home', [SpaceController::class, 'home'])->middleware("slashes:add");
Route::get('/pet', [SpaceController::class, 'home'])->middleware("slashes:add");
Route::get('/settings', [SpaceController::class, 'home'])->middleware("slashes:add");

Route::get('/', [root::class, 'index2'])->middleware("slashes:add")->name('home');
// Route::get('/access', [root::class, 'access'])->middleware("slashes:add")->name('access');
Route::get('/access', function () {
  // Only authenticated users may enter...
  $user = User::where('email', Auth::user()->email)->first();

  $unique_session = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/strlen($x)) )),1,32);
        // $token = $user->createToken('myapptoken')->plainTextToken;
        $session = sessions::where('user_id', Auth::user()->getId())->pluck('SWSID')->first();
        if ($session == null)
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
            sessions::where('user_id', $user->id)->update(['SWSID' => $unique_session]);
        }
        // $request->session()->put('id', $unique_session);
        // \Session::put('id', $unique_session);
        session([
            'id' => $unique_session,
            'user' => $user->id,
            'avatar' => $user->id
        ]);
  return redirect()->route('test_profile');

})->middleware('auth.basic');
Route::group(['middleware' => ['auth:sanctum']], function ()
{
    Route::get('/profile', [root::class, 'profile2'])->middleware("slashes:add")->name('test_profile');

});

Route::get('/invite/{code}', [root::class, 'invite'])->middleware("slashes:add")->name('icode');
Route::get('/invite', [root::class, 'invite'])->middleware("slashes:add")->name('invite');
//Route::get('/profile', [root::class, 'profile'])->middleware("slashes:add");
Route::get('/register', [root::class, 'register'])->middleware("slashes:add");
Route::get('/register/preload', [root::class, 'register-preload'])->middleware("slashes:add");
Route::get('crossdomain.xml', function()
{
  return ('<?xml version="1.0"?>
  <!DOCTYPE cross-domain-policy SYSTEM "http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd">
  <cross-domain-policy>
    <site-control permitted-cross-domain-policies="all"/>
    <allow-access-from domain="*.deploy-sw.com" to-ports="*" secure="true"/>
  </cross-domain-policy>
  ');
});

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

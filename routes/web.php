<?php

use App\Http\Controllers\SpaceController;
use App\Http\Controllers\root;
use App\Http\Controllers\AmfphpController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\InvitesController;

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
Route::get('/vueLogin', [root::class, 'vueLogin']);

Route::get('/space/{id}/',[SpaceController::class, 'index'])->middleware("slashes:add");
Route::get('/space/{id}/{instanceId}',[SpaceController::class, 'index'])->middleware("slashes:add");

Route::get('/shops/{id}/',[SpaceController::class, 'index'])->middleware("slashes:add");
Route::get('/login', [root::class, 'login'])->name('login');
// Auth::routes();

Route::get('/home/{id}', [SpaceController::class, 'index'])->middleware("slashes:add");
Route::get('401', [root::class, 'err401'])->middleware("slashes:add");
Route::get('403', [root::class, 'err403'])->middleware("slashes:add");

Route::get('/home', [SpaceController::class, 'home'])->middleware("slashes:add");
Route::get('/pet', [SpaceController::class, 'home'])->middleware("slashes:add");
Route::get('/settings', [SpaceController::class, 'home'])->middleware("slashes:add");

Route::get('/', [root::class, 'dash'])->middleware("slashes:add")->name('home');
Route::get('/vt', [root::class, 'vueTest'])->middleware("slashes:add");

Route::get('setsso', [root::class, 'setsso'])->middleware("slashes:add");
Route::get('/swm', [root::class, 'swm'])->middleware("slashes:add");
Route::get('/code/{invite}', [InvitesController::class, 'webCheckInvite']);
Route::get('/download', [InvitesController::class, 'downloads'])->name('downloads');
Route::get('/app-invite', [root::class, 'web_invite']);

// Route::get('/access', [root::class, 'access'])->middleware("slashes:add")->name('access');
Route::get('/signin', function (Request $request) {
    // var_dump($request->all());
    // if (session('invite'))
    // {
    //     Auth::loginUsingId(1);
    //     // session([
    //     //     'invite' => true,
            
    //     // ]);
    //     // var_dump("here");
    //     return redirect()->route('register');
    // }
    $update = false;
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    // php compare two strings and see if one contains YOUR_CUSTOM_AGENT
    if (strpos($u_agent, 'YOUR_CUSTOM_AGENT') !== false){
      $update = true;
        session([
            'update' => true,
        ]);
    }
    
    
    if ($update)
        return redirect()->route('downloads');
  // Only authenticated users may enter...
  $user = User::where('email', Auth::user()->email)->first();
  session()->regenerate();
  $cookie_url = \Config::get('custom.cookie_url');

  $unique_session = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(32/strlen($x)) )),1,32);
  
        // $token = $user->createToken('myapptoken')->plainTextToken;
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
          
  // ipv6 fix
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
                    'expires_at' => now()->addMinutes(300)
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
                    'expires_at' => now()->addMinutes(300)

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
  return redirect()->route('home')->cookie('SWSSID', $unique_session, null);

})->middleware('auth.basic')->name('access');
    Route::get('/panel/spintowin', [root::class, 's2w'])->middleware("slashes:add");

Route::group(['middleware' => ['swHeader']], function ()
{
    Route::get('/add/invite', [root::class, 'addInvite'])->middleware("slashes:add")->name('addInvites');
    Route::get('/profile', [root::class, 'vueLogin'])->middleware("slashes:add")->name('profile');
    Route::get('/ogprofile', [root::class, 'profile'])->middleware("slashes:add")->name('ogprofile');
    Route::get('/panel/items/wearing', [root::class, 'itemsworn'])->middleware("slashes:add");
    Route::get('/panel/plant', [root::class, 'plant']);
    Route::get('/panel/pet', [root::class, 'petPanel']);
    
    Route::get('/vue', [root::class, 'vue']);


    Route::get('/profile/avatar/create', [root::class, 'avatarCreate']);
    Route::get('/avatar/create', [root::class, 'avatarCreate']);

    // 2010 
    Route::get('/console', [root::class, 'dash'])->middleware("slashes:add")->name('console');
    Route::get('/dash', [root::class, 'dash'])->middleware("slashes:add")->name('dash');
    Route::get('/dashboard', [root::class, 'dash'])->middleware("slashes:add")->name('dashboard');

    Route::get('/homepage/game/arcade', [root::class, 'home_arcade'])->middleware("slashes:add");
    Route::get('/homepage/cp', [root::class, 'home_cp'])->middleware("slashes:add");
    Route::get('/homepage/loyalty', [root::class, 'home_loyalty'])->middleware("slashes:add");
    Route::get('/homepage/places', [root::class, 'home_places'])->middleware("slashes:add");
    Route::get('/homepage/xp', [root::class, 'home_xp'])->middleware("slashes:add");


    Route::get('/manager', [root::class, 'swmod'])->middleware("slashes:add");
    Route::get('/smi', [root::class, 'swmod'])->middleware("slashes:add");
    Route::get('/swmod', [root::class, 'swmod'])->middleware("slashes:add");
    Route::get('/swadmin', [root::class, 'swadmin'])->middleware("slashes:add");
    Route::get('/swadmin/config.xml', function ()
    {
        return response()->download(storage_path('public/swadmin/config.xml'));
    });


    Route::get('/upload', [root::class, 'upload'])->middleware("slashes:add")->name('upload');
    Route::get('/add/item/model', [root::class, 'aitembymodel'])->middleware("slashes:add")->name('additem');
    Route::post('/add/item/model', [root::class, 'aitembymodel'])->middleware("slashes:add");
    Route::get('/add/space/models', [root::class, 'spaceModels'])->middleware("slashes:add");

    Route::get('/add/space/models/{id}', [root::class, 'spaceModels'])->middleware("slashes:add");

    Route::get('/all/items', [root::class, 'allitems'])->middleware("slashes:add");
    Route::get('/missing/items', [root::class, 'missitems'])->middleware("slashes:add");

    Route::get('/conf' , [root::class, 'conf'])->middleware("slashes:add");

    Route::get('/fix/items/?file={file}', [root::class, 'fixitems'])->middleware("slashes:add");
    Route::get('/fix/items', [root::class, 'fixitems'])->middleware("slashes:add");
    Route::post('/fix/items', [root::class, 'fixitems'])->middleware("slashes:add");
});



Route::get('/invite/{code}', [root::class, 'invite'])->middleware("slashes:add")->name('icode');
// Route::get('/invite', [root::class, 'invite'])->middleware("slashes:add")->name('invite');
//Route::get('/profile', [root::class, 'profile'])->middleware("slashes:add");
Route::get('/register', [root::class, 'register'])->middleware("slashes:add")->name('register');
Route::get('/register/preload', [root::class, 'register-preload'])->middleware("slashes:add");
Route::get('crossdomain.xml', function()
{
  return ('<?xml version="1.0"?>
  <!DOCTYPE cross-domain-policy SYSTEM "http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd">
  <cross-domain-policy>
    <site-control permitted-cross-domain-policies="all"/>
    <allow-access-from domain="*" to-ports="*" secure="true"/>
  </cross-domain-policy>
  ');
});

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
 

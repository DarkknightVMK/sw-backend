<?php

use App\Http\Controllers\AmfphpController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CatalogItemEntryController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\root;
use App\Http\Controllers\PetController;
use App\Http\Controllers\InvitesController;
use App\Http\Controllers\S2WController;
use App\Http\Controllers\Utils;
use App\Models\avatarItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Avatars;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//Public Routes
//Route::resource('products', ProductController::class);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('auth.basic');
Route::post('/auth/token', [AuthController::class, 'loginWithToken']);
Route::post('/auth/invite', [AuthController::class, 'invite']);
Route::post('/auth/justRegistered', [AuthController::class, 'justRegistered']);
Route::post('/auth/forgot-password', [PasswordController::class, 'forgotPassword']);
Route::post('/auth/verify-reset-code', [PasswordController::class, 'verifyResetCode']);
Route::post('/auth/reset-password', [PasswordController::class, 'resetPassword']);


Route::middleware('auth:sanctum')->post('/user/serverip', [AuthController::class, 'ip']);
Route::middleware('auth:sanctum')->post('/user/contentpath', [AuthController::class, 'cp']);
Route::middleware('auth.basic')->post('/auth/logout', [AuthController::class, 'logout']);

Route::get('/products/search/{name}', [ProductController::class, 'search']);
//Route::get('/products/search/{name}', [ProductController::class, 'search']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

//Route::get('/user/me', [AuthController::class, 'index']);
Route::get('/user/emailavailable/{email}', [AuthController::class, 'emailAvail']);


// UNCOMMENT THIS TO ENABLE USER REGISTRATION
// if (session('invite'))  
    Route::post('/user/make/join', [RegisterController::class, 'register']);
// Route::post('/user/join', [AuthController::class, 'register']);

Route::post('/filter/validate', [RegisterController::class, 'filter_validate']);

Route::post('/avatar/nameavailable', [RegisterController::class, 'name_available']);
Route::post('/avatar/createAvatar', [RegisterController::class, 'create_avatar']);
Route::get('/avatar/findtotalonline', [AvatarController::class, 'online']);
Route::get('/avatar/head', [AvatarController::class, 'randomHead']);
Route::get('/avatar/head/{email}', [AvatarController::class, 'randomHead']);
Route::get('/me', [AvatarController::class, 'userme']);
Route::post('/avatar/updateFace/{aid}', [AvatarController::class, 'updateFace']);
Route::post('/user/save/photo', [UserController::class, 'savePhoto']);

Route::post('/swds/gateway', [AmfphpController::class, 'amfphp']);


//Route::get('/space/models/{xml}', [SpaceController::class, 'models']);

// Route::get('/user/me', [AvatarController::class, 'me']);


Route::get('/space/time', [SpaceController::class, 'time']);
Route::post('/access', [AuthController::class, 'check']);
Route::post('/access-admin', [AuthController::class, 'admin_check']);
Route::post('/access/{swsid}/', [AuthController::class, 'sso']);
Route::post('/item/icon', [ItemsController::class, 'icon']);

Route::get('/items/basic/{id}', [ItemsController::class, 'createBasicItems']);
Route::get('/items/models/{id}', [ItemsController::class, 'getModel']);
Route::get('/space/{id}', [SpaceController::class, 'avatarSpace']);

Route::get('/space/name/{type}/{id}', [SpaceController::class, 'spaceName']);
Route::get('/space/desc/{url}', [SpaceController::class, 'spaceDesc']);

Route::post('/items/make/{id}', [ItemsController::class, 'storeModel']);
Route::post('/models/{id}', [SpaceController::class, 'storeModel']);

Route::middleware('swHeader')->get('/user/me', [AvatarController::class, 'userme']);
Route::middleware('swHeader')->get('/space/config/{id}', [SpaceController::class, 'spaceConfig']);

Route::get('/pets', [PetController::class, 'index']);
Route::get('/avi/{aid}', [AvatarController::class, 'aviID']);
Route::get('/avis', [AvatarController::class, 'avis']);
Route::get('/online', [AvatarController::class, 'apiOnline']);
Route::get('/pet/{id}', [AvatarController::class, 'pet']);
Route::get('/avatar/pet/{id}', [AvatarController::class, 'petDetail']);
Route::post('/avatar/pet/{id}', [AvatarController::class, 'updatePetDetails']);

Route::get('/world/online', [AvatarController::class, 'worldOnline']);

Route::post('/invite', [InvitesController::class, 'checkInvite']);
Route::post('/swds/gateway;jsessionid={session}', [AmfphpController::class, 'amfphp']);

// VUE ROUTES PROTECT LATER
Route::get('/s2w/categories', [S2WController::class, 'getCategories']);
Route::get('/s2w/prices', [S2WController::class, 'getPrices']);
Route::get('/s2w/timing', [S2WController::class, 'getTiming']);
Route::post('/s2w/timing', [S2WController::class, 'saveTiming']);
Route::get('/items', [ItemsController::class, 'index']);

//Protected Routes
Route::group(['middleware' => ['swHeader']], function () 
{
    Route::post('/avatar/updateAvatar', [AvatarController::class, 'update_avatar']);
    Route::post('/avatar/createAvatar', [RegisterController::class, 'create_avatar']);

    Route::post('/upload/image/', [UploadController::class, 'store'])->name('upload.image');
   
    Route::post('/upload/content', [UploadController::class, 'content'])->name('upload.content');

    Route::post('/compress', Utils::class.'@compress')->name('compress');
    Route::post('/decompress', Utils::class.'@decompress')->name('decompress');
    Route::get('/inventory' , [ItemsController::class, 'getInventory']);
    Route::post('/gift', [ItemsController::class, 'gift']);
    // Route::post('/items/add/{id}', [ItemsController::class, 'storeModel']);
    
    //  php memory hog, but it works. keep commented when not needed
    // Route::get('/generate/invites', [root::class, 'makeInvites']);
    // Route::get('/generate/invites/{email}', [root::class, 'makeInvites']);
    Route::post('/make/invite', [InvitesController::class, 'make']);
    
    
    Route::get('/space/models', [SpaceController::class, 'showModel']);
    Route::get('/space/models/{id}', [SpaceController::class, 'findModel']);
    Route::get('/spaces/mine', [SpaceController::class, 'mySpaces']);
    Route::get('/spaces/favs', [SpaceController::class, 'myFavs']);
    Route::get('/spaces/me', [SpaceController::class, 'meSpaces']);
    Route::get('/spaces/popular', [SpaceController::class, 'popSpaces']);
    Route::get('/items/generate/{id}/{model}', [ItemsController::class, 'createItemsbyModel']);
    Route::get('/items/make/all/{id}', [ItemsController::class, 'generateAll']);
    Route::get('/catalog', [CatalogItemEntryController::class, 'index']);
    Route::get('/session', [AuthController::class, 'session']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    // Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/avatar/selected', [AvatarController::class, 'selected']);
    Route::post('/avatar/header', [AvatarController::class, 'header']);
    Route::post('/avatar/experiment', [AvatarController::class, 'experiment']);
    Route::post('/avatar/chooseAvatar/{aid}', [AvatarController::class, 'chooseAvatar']);
    Route::post('/avatar/deleteAvatar/{aid}', [AvatarController::class, 'deleteAvatar']);
    Route::post('/avatar/makeDefaultAvatar/{aid}', [AvatarController::class, 'makeDefaultAvatar']);
    Route::get('/avatar/{aid}', [AvatarController::class, 'avatarDetails']);
    Route::post('/avatar/updateTakePet', [AvatarController::class, 'updateTakePet']);
    Route::get('/plant/check', [AvatarController::class, 'checkPlant']);
    Route::get('/spintowin/check', [AvatarController::class, 'checkSpin']);
    Route::get('/balance', [AvatarController::class, 'balance']);
    Route::post('/toggleItem', [ItemsController::class, 'toggle']);
    Route::get('/invite/{email}', [AuthController::class, 'inviteGenerate']);
        
});

Route::middleware('swHeader')->get('/user', function (Request $request) 
{
    return $request->user();
});

<?php

namespace App\Http\Controllers;
require_once(__DIR__ . '../../../../resources/php/config.php');

use App\Models\spaceModels;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use App\Models\avatarSpaces;
use App\Models\Avatars;
use App\Models\onlineUsers;
use App\Models\spaceFavorites;
use App\Models\catalogSpaceEntry;
use App\Models\catalogCategories;
use Illuminate\Support\Str;

class SpaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $space = true;
        $user = (Auth::check()) ? Users::where('id', Auth::user()->id)->first() : null;
        $secondaryGroups = (Auth::check()) ? explode(',', $user->secondaryGroupIds) : null;
        $hasHeader = ( $secondaryGroups != null) ? in_array(18, $secondaryGroups) : false;
        if (Auth::check() &&  $hasHeader)
            return view('origspace', compact('id', 'space', 'user'));
        elseif(Auth::check())
            return view('spacebottomui', compact('id', 'space'));
        else
            return view('index');
    
    }

    public function mySpaces(Request $request)
    {
        $user = Auth::user();
        $spaces = avatarSpaces::where('user_id', $user->id)->get();
        for ($i = 0; $i < count($spaces); $i++)
        {
            $spaces[$i]["currentVisitors"] = onlineUsers::where('space_id', $spaces[$i]["id"])->where('online',true)->where('local', false)->count();
            // add the rest of onlineUser data to the space
            $spaces[$i]["visitors"] = onlineUsers::where('space_id', $spaces[$i]["id"])->where('online',true)->where('local', false)->get();
        }
        // return a json object with the space data and current visitor data
        // paginate this

        return $spaces;

    }

    public function myFavs(Request $request)
    {
        $user = Auth::user();
        // $spaces = array();
        $spaces = spaceFavorites::where('avatar_id', $user->defaultAvatar)->get();
        //join avatarSpaces to spaceFavorites on space_id
        // $spaces = avatarSpaces::where('user_id', $user->id) ->join('space_favorites', 'avatar_spaces.id', '=', 'space_favorites.space_id')->get();
        foreach($spaces as $space)
        {
            $spaces = avatarSpaces::find($space->space_id);
            $spaces->currentVisitors = onlineUsers::where('space_id', $space->space_id)->where('online',true)->where('local', false)->count();
            $spaces->visitors = onlineUsers::where('space_id', $space->space_id)->where('online',true)->where('local', false)->get();

        }
  
        return array($spaces);
    }

    public function meSpaces(Request $request)
    {
        //combine mySpaces and myFavs
        $user = Auth::user();
        $spaces = avatarSpaces::where('user_id', $user->id)->get()->toArray();
        $spaceFavs = spaceFavorites::where('avatar_id', $user->defaultAvatar)->get();
        // id = 119 for regular, 122 for thanksgiving, 123 for christmas, 4 for halloween
        $featuredSpace = catalogSpaceEntry::where('categoryId', 119)->get();
        $featured = array();
        foreach ($featuredSpace as $space)
        {
            $featured[] = avatarSpaces::find($space->spaceId);
            //icon for featured spaces

            
        }
        // split every 16 items into a new array
        $featured = array_chunk($featured, 16);
        // icon in $featured
        foreach($featured as $space)
        {
            foreach($space as $s)
            {
                if (!empty($s->icon)) {
                    $s->icon = 'https://media.'.SITE_DOMAIN.'/images/space/' . $s->icon;
                }
            }
        }

        // $featured;
        // $spaces = array();
        for ($i = 0; $i < count($spaces); $i++)
        {
            $spaces[$i]["currentVisitors"] = onlineUsers::where('space_id', $spaces[$i]["id"])->where('online',true)->where('local', false)->count();
            // add the rest of onlineUser data to the space
            $spaces[$i]["visitors"] = onlineUsers::where('space_id', $spaces[$i]["id"])->where('online',true)->where('local', false)->get();
            if (!empty($spaces[$i]["icon"])) {
                $spaces[$i]["icon"] = 'https://media.'.SITE_DOMAIN.'/images/space/' . $spaces[$i]["icon"];
            }
        }
        $spaces = array_chunk($spaces, 16);

        foreach($spaceFavs as $space)
        {
            $space->space = spaceFavorites::find($space->space_id)->space;
            // $spaceFavs->currentVisitors = onlineUsers::where('space_id', $space->space_id)->where('online',true)->where('local', false)->count();
            // $spaceFavs->visitors = onlineUsers::where('space_id', $space->space_id)->where('online',true)->where('local', false)->get();
            if (!empty($space->space->icon)) {
                $space->space->icon = 'https://media.'.SITE_DOMAIN.'/images/space/' . $space->space->icon;
            }

        }        
        $fav = array();
        foreach($spaceFavs as $space)
        {
            $fav[] = $space->space;
        }
        $fav = array_chunk($fav, 16);

        $arr = array(
            'mySpaces' => $spaces,
            'myFavs' => $fav,
            'featured' => $featured
        );
        
        return $arr;
    }

    public function popSpaces(Request $request)
    {


        $popular = array();
        $online = onlineUsers::where('online', true)->where('local',false)->get()->toArray();
        $duplicates = array_count_values(array_column($online, 'space_id'));
        $spaceId = array_keys($duplicates);
        foreach ($spaceId as $key => $value) {
            $space = avatarSpaces::where('id', $value)->first();
            $spaceModel = spaceModels::where('model_id', $space->modelId)->first();
            if ($space->showInPlacePanel == true) {
                $popular[] = array
                (
                    'id' => $space->id,
                    'name' => $space->name,
                    'desc' => $space->desc,
                    'modelId' => $space->modelId,
                    'modelPrice' => $space->modelPrice,
                    'iconSource' => $space->iconSource,
                    'spaceThumbnailSource' => $space->spaceThumbnailSource,
                    'currentVisitors' => onlineUsers::where('space_id', $space->id)->where('online',true)->where('local', false)->count(),
                    'visitors' => onlineUsers::where('space_id', $space->id)->where('online',true)->where('local', false)->get(),
                    'model' => $spaceModel,
                    'fav' => false,
                );
            }
        }
        $popular = array_chunk($popular, 16);

        return array(
            'popular' => $popular);
    }

    public function avatarSpace(Request $request, $id)
    {
        // return avatarSpaces::where('space_id', $id)->get();
        // return 
        $space = avatarSpaces::find($id);
        if ($space != null)
        return $space;
        else
        return \response(
            [
                'error' => 'Space not found'
            ],
            404);
    }

    public function spaceName(Request $request, $type, $id)
    {
        // url could be /space or /home
        // get first half of url /space or /home and call this the name
        // get second half of url /space or /home and call this the id
        // $url = explode('/', $url);
        // $name = $url[1];
        // $id = $url[count($url) - 1];
        switch ($type)
        {
            case 'space':
                // is number or string?
                if (is_numeric($id))
                    $space = avatarSpaces::find($id);
                else
                    $space = avatarSpaces::where('alias', $id)->first();
                if ($space != null)
                return \response(
                    [
                       'name' => $space->name,
                       'desc' => (!empty($space->desc)) ? $space->desc : "-No Description Provided-",
                    ],
                    200);
                else
                return \response(
                    [
                        'error' => 'Space not found'
                    ],
                    404);
                break;
            case 'home':
                $avatar = avatars::where('fullName', $id)->first();
                if ($avatar != null){
                    $space = avatarSpaces::find($avatar->homeSpaceId);
                    if ($space != null)
                    return \response(
                        [
                           'name' => $space->name,
                           'desc' => (!empty($space->desc)) ? $space->desc : "-No Description Provided-",
                 ],
                        200);
                    else
                    return \response(
                        [
                            'error' => 'Space not found'
                        ],
                        404);
                }
                else
                return \response(
                    [
                        'error' => 'Avatar not found'
                    ],
                    404);
                break;

        }


    }

    public function spaceConfig(Request $request, $id)
    {
        $SWSID = $request->header('SWSID');
        $space = avatarSpaces::find($id);
        function getSpaceInfo($id, $space, $json = null)
		{
			// homespace
			if (!is_numeric($id) && $id != null)
			{
					$homeSpaceId = Avatars::where('fullName', 'LIKE', '%'. $id. '%')->pluck('homeSpaceId')->first();
					$alias = avatarSpaces::where('alias', $id)->pluck('id')->first();

					if ($homeSpaceId != null)
					{
						$user_json = avatarSpaces::where('id', $homeSpaceId)->get()->toJson();
						//return $user_json;
						preg_match_all('/\{(?:[^{}]|(?R))*\}/', $user_json, $jsondecode);
						$user = json_decode($jsondecode[0][0], true);
						if ($json == null)
								return $user;
						return $user[$json];
					}
					elseif ($homeSpaceId == null && $alias != null)
					{
						$user_json = avatarSpaces::where('id', $alias)->get()->toJson();
						//return $user_json;
						preg_match_all('/\{(?:[^{}]|(?R))*\}/', $user_json, $jsondecode);
						$user = json_decode($jsondecode[0][0], true);
						if ($json == null)
								return $user;
						return $user[$json];
					}
					return null;
			}

			elseif (is_numeric($id)) {
				$user_json = avatarSpaces::where('id', $id)->get()->toJson();
				//return $user_json;
				// var_dump($user_json);
				if ($user_json == null) // No spaces exist in DB
				{
					return "";
				}
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $user_json, $jsondecode);
        $user = json_decode($jsondecode[0][0], true);
        if ($json == null)
            return $user;
        return $user[$json];
			}
			 else
			 	return "";

		}

        function getUser($json = null)
        {
            $user = Auth::user();
            $user_json = Users::where('id', $user->id)->get()->toJson();
            preg_match_all('/\{(?:[^{}]|(?R))*\}/', $user_json, $jsondecode);
            $user = json_decode($jsondecode[0][0], true);
            if ($json == null)
                return $user;
            return $user[$json];
        }

        $userAuth = (Auth::check()) ? Users::where('id', Auth::user()->id)->first() : null;
if ($userAuth != null)
{
$secondaryGroups = explode(',', $userAuth->secondaryGroupIds);

// EXPERIMENTAL
if (in_array(20, $secondaryGroups))
{
    define("GATEWAY_URI", rtrim(SITE_URI, "/") . "/local/swds/gateway");
} 
else if (in_array(21, $secondaryGroups))
{
    define("GATEWAY_URI", rtrim(SITE_URI, "/") . "/java/swds/gateway");
}
else {
define("GATEWAY_URI", rtrim(SITE_URI, "/") . "/swds/gateway");
}

// }// 
// $user = Users::where('id', Auth::user()->id)->first();

// if (Auth::check() && $user->id == 1)
// if (in_array(20, $secondaryGroups))
// define("SMI_GATEWAY_URI", rtrim(SITE_URI, "/") . "/smi/gateway");
// else
// define("SMI_GATEWAY_URI", rtrim(SITE_URI, "/") . "/smi/swds/gateway");
}
        $user = getUser();
$userId = $user['id'];
        return \response(
            [
                'preloader' => ''. CONTENT_CONTENT.'main.swf',
                'flashvars' => 'config=<config>
	<user>
		<id>'. $userId.'</id>
		<justRegistered>N</justRegistered>
		<isNew>N</isNew>
		<isVIP>N</isVIP>
	</user>
	<space>
		<id>'.getSpaceInfo($id, $space,'id').'</id>
		<name>'.getSpaceInfo($id, $space,'name').'</name>
		<modelId>'.getSpaceInfo($id, $space, 'modelId').'</modelId>
		<snapshotFile>'.getSpaceInfo($id, $space, 'spaceSnapShotSource').'</snapshotFile>
	</space>
	<webServiceManager>
		<dataService>
			<url>'.GATEWAY_URI.';jsessionid='.$SWSID.'</url>
			<serviceName>ds</serviceName>
			<timeout>10000</timeout>
			<enableSecureCalls>false</enableSecureCalls>
		</dataService>
		<messageService>
			<timeout>30000</timeout>
		</messageService>
	</webServiceManager>
	<misc>
		<singleBrowserOnly>true</singleBrowserOnly>
		<loadContentFromPackages>false</loadContentFromPackages>
	</misc>
	<paths>
		<webDomain>'.SITE_DOMAIN.'</webDomain>
		<rootPath>'.rtrim(SITE_URI, '/').'</rootPath>
		<consolePath>'.SITE_URI.'profile/</consolePath>
		<storePath>'.SITE_URI.'store/</storePath>
		<newsPath>'.SITE_URI.'news/</newsPath>
		<forumPath>'.SITE_URI.'forum/</forumPath>
		<helpPath>'.SITE_URI.'help/</helpPath>
		<supportPath>'.SITE_URI.'support/</supportPath>
		<settingsPath>'.SITE_URI.'settings/</settingsPath>
		<getTokensPath>'.SITE_URI.'store/</getTokensPath>
		<citizenLevelInfoPath>'.SITE_URI.'help/faq/citizen-levels/</citizenLevelInfoPath>
		<skillsInfoPath>'.SITE_URI.'help/faq/levels-xp/</skillsInfoPath>
		<attributesInfoPath>'.SITE_URI.'help/faq/avatar-attributes/</attributesInfoPath>
		<clientPath>'.CONTENT_CONTENT.'main.3280.swf</clientPath>
		<contentPath>'.CONTENT_CONTENT.'</contentPath>
		<packagePath>'.CONTENT_CONTENT.'packages/</packagePath>
		<libraryPath>'.CONTENT_CONTENT.'assets/</libraryPath>
		<configPath>'.SITE_URI2.'config/</configPath>
		<mediaPath>'.MEDIA_URI.'</mediaPath>
		<themePath>'.CONTENT_CONTENT.'themes/</themePath>
		<avatarImagesPath>'.AVATARS_URI.'</avatarImagesPath>
		<spaceImagesPath>'.MEDIA_URI.'images/space/</spaceImagesPath>
		<widgetImagesPath>'.WIDGETS_URI.'</widgetImagesPath>
		<homespacePath>'.SITE_URI.'home/</homespacePath>
		<petTrainingPath>'.SITE_URI.'space/pettraining/</petTrainingPath>
		<plantNurseryPath>'.SITE_URI.'space/gardenlife/</plantNurseryPath>
		<tradingPostPath>'.SITE_URI.'space/tradingpost/</tradingPostPath>
		<buySellForumPath>'.SITE_URI.'forum/forums/66-Buy-amp-Sell</buySellForumPath>
	</paths>
</config>',
            ],
            200);

    }

    public function test(Request $request, $id)
    {
        $space = true;

        return view('testspace', compact('id', 'space'));
    }
    public function settings(Request $request)
    {
        $id = null;
        return view('pet', compact('id'));
    }

    public function home()
    {
        // echo $id;
        $space = false;
        return view('space', compact( 'space'));
    }

    public function time()
    {
        date_default_timezone_set('US/Eastern');
        $hrs = date('h');
        $mins = date('i');
        return \response(
            [
                'hours' => $hrs,
                'minutes' => $mins
            ],
            200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }
    public function showModel()
    {
        return spaceModels::pluck('model_id');

    }
    public function findModel($id)
    {
        return spaceModels::where('model_id', $id)->get();
    }

    public function storeModel(Request $request)
    {
        $fields = $request->validate(
            [
                'id.0' => 'required|string',
                'name.0' => 'required|string',
                'desc.0' => 'required|string',
                'source' => 'required|string',


            ]
        );

        $model = spaceModels::create(
            [
                'model_id' => intval($fields['id']['0']),
                'model_desc' =>$fields['desc']['0'],
                'model_details' =>$fields['name']['0'],
                'model_source' =>$fields['source'],
                'model_price' => isset($request['price']['0']) ? $request['price']['0'] : null,
            ]
        );
        $response = [
            $model
            ];
        return response($response, 201);
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

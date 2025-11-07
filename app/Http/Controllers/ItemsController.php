<?php

namespace App\Http\Controllers;
require_once(__DIR__ . '../../../../resources/php/config.php');
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\items;
use App\Models\avatarItems;
use App\Models\Avatars;
use App\Models\avatarWearing;
use Illuminate\Support\Facades\Storage;

use function App\Http\Controllers\create_guid as ControllersCreate_guid;

class ItemsController extends Controller
{


    public function index()
    {
        $items = items::all();
        //edit modelSource and append url to it
        foreach ($items as $item) {
            // append CONTENT_CONTENT for model_source and model_icon
            $item->model_source = CONTENT_CONTENT . $item->model_source;
            $item->model_icon = CONTENT_CONTENT . $item->model_icon;

        }
        return response()->json($items->toArray());

    }
    public function getModel($id)
    {
        return items::where('model_id', $id)->get()->first();
    }

    public function toggle(Request $request)
    {
        $item = avatarWearing::where('item_id', $request->item_id)->first();

        if ($item->avatar_id != $request->avatar_id) {
            return response()->json(['error' => 'You can only use your own items.']);
        }
        else if ($item->exists()) {

            // avatarItems::where('item_id', $item->item_id)->where('use', $item->use)->update(['use' => false]);
            // delete 
            $item::where('item_id', $item->item_id)->where('avatar_id', $item->avatar_id)->delete();
            // $item->save();
            return response()->json(['success' => 'Item disabled.']);
        } else {

            // avatarItems::where('use', $item->use)->where('avatar_id', $request->avatar_id )->update(['use' => true]);
            // $item->save();
            // return response()->json(['success' => 'Item enabled.']);
        }
        // $item->save();
        // return response()->json(['status' => 'success']);
    }

    public function icon(Request $request)
    {
        if ($request->filename != null && $request->image != null) {
            Storage::disk('widgets')->put( $request->filename,  base64_decode($request->image));
            return response()->json([
                'status' => 'success',
            ], 200);
        }
        return response()->json([
            'status' => 'error',
        ], 400);
        
    }


    public function storeModel(Request $request)
    {
        $fields = $request->validate(
            [
                'cid' => 'required|string',
                'id.0' => 'required|string',
                'name.0' => 'required|string',
                'tags.0' => 'required|string',
                'icon.0' => 'string',
                'desc.0' => 'string',
                'source' => 'required|string',
                'minLevel.0' => 'string',
                'price.0' => 'string',
                'tokens.0' => 'string',
                'premium.0' => 'string',
                'action' => 'string',
                'hasAccessSecurity' => 'string',
                'action_attributes' => 'string',
                'action_attributes_secondary' => 'string',
                'use_path' => 'string',
                'baseItemId' => 'string',

            ]
        );

        $model = items::create(
            [
                'model_cid' => $fields['cid'],
                'model_id' => intval($fields['id']['0']),
                'model_desc' =>$fields['name']['0'],
                'model_details' => isset($fields['desc']['0']) ? $fields['desc']['0'] : null,
                'model_source' =>$fields['source'],
                'model_tags' => $fields['tags']['0'],
                'model_icon' => isset($fields['icon']['0']) ? $fields['icon']['0'] : null,
                'model_price_gold' => isset($fields['price']['0']) ? intval($fields['price']['0']) : null,
                'model_price_tokens' => isset($fields['tokens']['0']) ? intval($fields['tokens']['0']) : null,
                'model_min_xplevel' => isset($fields['minLevel']['0']) ? intval($fields['minLevel']['0']) : 1,
                'model_premium_only' => isset($fields['premium']['0']) == "Y" ? true : false,
                // 'action_path' => isset($fields['action']) ? $fields['action'] : null,
                'hasAccessSecurity' => isset($fields['hasAccessSecurity']) ? filter_var($fields['hasAccessSecurity'], FILTER_VALIDATE_BOOLEAN) : false,
                'action_attributes' => isset($fields['action_attributes']) ? $fields['action_attributes'] : null,
                'action_attributes_secondary' => isset($fields['action_attributes_secondary']) ? $fields['action_attributes_secondary'] : null,
                'use_path' => isset($fields['use_path']) ? $fields['use_path'] : null,
                'baseItemId' => $fields['baseItemId'],
            ]
        );
        $response = [
            $model
            ];
        return response($response, 201);
    }
    public function createBasicItems($id)
    {
        function create_guid()
        {
            $charid = md5(uniqid(mt_rand(), true));
            $hyphen = chr(45);// "-"
            $uuid =
                substr($charid, 0, 8)
                . substr($charid, 8, 4)
                . substr($charid, 12, 4)
                . substr($charid, 16, 4);
            return $uuid;
        }
       function createItems($item_id, $ic, $model_id, $aid)
       {
        avatarItems::create(
            [
                'item_id' => $item_id,
                'model_id' => $model_id,
                'item_count' => $ic,
                'avatar_id' => $aid,
                'item_config' => '',
            ]
        );
       } 
       $response = [
        createItems(create_guid(), 1, 1830, $id),
        createItems(create_guid(), 1, 1811, $id),
        createItems(create_guid(), 1, 1793, $id),
        createItems(create_guid(), 1, 3924, $id),
        createItems(create_guid(), 1, 2091, $id),
        createItems(create_guid(), 1, 2092, $id),
        createItems(create_guid(), 1, 2093, $id),
        createItems(create_guid(), 1, 2094, $id),
        createItems(create_guid(), 1, 2095, $id),
        createItems(create_guid(), 1, 2096, $id),
        createItems(create_guid(), 1, 2097, $id),
        createItems(create_guid(), 1, 2098, $id),
        createItems(create_guid(), 1, 2099, $id),
        createItems(create_guid(), 1, 2100, $id),
        createItems(create_guid(), 1, 2101, $id),
        createItems(create_guid(), 1, 2102, $id),
        createItems(create_guid(), 1, 2103, $id),
        createItems(create_guid(), 1, 2104, $id),


        ];
    return response($response, 201);
    }

    public function generateAll(Request $request)
    {
        $avatar = Avatars::where('avatar_id', $request->id)->first();
        $avatar_items = avatarItems::where('avatar_id', $request->id)->get();
        $items = items::all();
        // var_dump($items);
        function item_id()
        {
            $charid = md5(uniqid(mt_rand(), true));
            $hyphen = chr(45);// "-"
            $uuid =
                substr($charid, 0, 8)
                . substr($charid, 8, 4)
                . substr($charid, 12, 4)
                . substr($charid, 16, 4);
            return $uuid;
        }
        foreach ($items as $item) {
            $charid = md5(uniqid(mt_rand(), true));
            $hyphen = chr(45);// "-"
            $uuid =
                substr($charid, 0, 8)
                . substr($charid, 8, 4)
                . substr($charid, 12, 4)
                . substr($charid, 16, 4);
            avatarItems::create(
                [
                    'item_id' => $uuid,
                    'model_id' => $item->model_id,
                    'item_count' => 1,
                    'avatar_id' => $avatar->avatar_id,
                    'item_config' => '',
                ]
            );
        }
        return response(201);

    }


    public function createItemsbyModel($id, $model)
    {
        function create_gui()
        {
            $charid = md5(uniqid(mt_rand(), true));
            $hyphen = chr(45);// "-"
            $uuid =
                substr($charid, 0, 8)
                . substr($charid, 8, 4)
                . substr($charid, 12, 4)
                . substr($charid, 16, 4);
            return $uuid;
        }
       function createItem($item_id, $ic, $model_id, $aid)
       {
        avatarItems::create(
            [
                'item_id' => $item_id,
                'model_id' => $model_id,
                'item_count' => $ic,
                'avatar_id' => $aid
            ]
        );
       } 
       $response = [
        createItem(create_gui(), 1, $model, $id)
    ];
    return response($response, 201);
}

    public function getInventory(Request $request)
    {
        $user = Auth::user();
        $avatar_items = avatarItems::where('user_id', $user->id)->get();
        // 						ctx.select().from(AVATAR_ITEMS).join(ITEMS).on(ITEMS.MODEL_ID.eq(AVATAR_ITEMS.MODEL_ID)).where(AVATAR_ITEMS.USER_ID.eq(UInteger.valueOf(avi.getOwner_id()))).and(AVATAR_ITEMS.SPACE_ID.isNull()).fetch();
        $join = avatarItems::join('items', 'items.model_id', '=', 'avatar_items.model_id')->where('avatar_items.user_id', $user->id)->whereNull('avatar_items.space_id')->get()->toArray();
        $response = [];
        foreach ($join as $item) {
            $response[] = [
                'item_id' => $item['item_id'],
                'model_id' => $item['model_id'],
                'item_count' => $item['item_count'],
                'avatar_id' => $item['avatar_id'],
                'item_config' => $item['item_config'],
                'model_cid' => $item['model_cid'],
                'model_desc' => $item['model_desc'],
                'model_details' => $item['model_details'],
                'model_source' => $item['model_source'],
                'model_tags' => $item['model_tags'],
                'model_icon' => $item['model_icon'],
                'model_price_gold' => $item['model_price_gold'],
                'model_price_tokens' => $item['model_price_tokens'],
                'model_min_xplevel' => $item['model_min_xplevel'],
                'model_premium_only' => $item['model_premium_only'],
                'hasAccessSecurity' => $item['hasAccessSecurity'],
                'action_attributes' => $item['action_attributes'],
                'action_attributes_secondary' => $item['action_attributes_secondary'],
                'use_path' => $item['use_path'],
                'baseItemId' => $item['baseItemId'],
            ];
        }



        
        
        return response($response, 200);
    }

}

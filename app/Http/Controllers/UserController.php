<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sessions;
use App\Http\Controllers\Utils;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function savePhoto(Request $request)
    {
        $bytearray = $request->photo;
        //verify session and uid
        $session = sessions::where('SWSID', $request->session)->where('user_id', $request->uid)->first();
        if (!$session->expires_at > now() || !$session) {
            return \response(
                [
                    'success' => false,
                    'message' => 'session or uid not found',
                ],
                404);
        }
        if(!Storage::disk('media')->exists($request->uid))
        {
            Storage::disk('media')->makeDirectory($request->uid);
        }
        $id = Utils::generateRandomString() . time();
        $filename = $request->uid.'/'. $request->uid . '_' . $id . '.jpg';
        //resize image
        
        $media_url = \Config::get('custom.media_url');


        $this->uploadFile($bytearray,   $filename);

     //TODO enable JPEG support
       
       $thumb = $request->uid.'/'. $request->uid . '_' .Utils::generateRandomString() . time() . '.png';
    //    sleep(3);
         $this->uploadFile($bytearray,   $thumb, $filename, true);


        return response()->json(['success' => true, 'photoUrl' => $media_url . '/'. $filename, 'thumbUrl' => $media_url . '/'. $thumb,
        'id' => Utils::generateRandomString() . time(), 'userId' => $request->uid, 'avatarId' => $request->aid], 200);
    }

    function uploadFile($file, $name,$prev = "", $thumb = false)
    {


        if ($thumb == true)
        {
            $img = \Image::make( Storage::disk('media')->get($prev)); 
            $img->resize(128, 96, function ($constraint) {
                $constraint->aspectRatio();
            })->encode('png');
            Storage::disk('media')->put( $name,  $img);

            // $img->move(str_replace(chr(0), '', Storage::disk('media')->get($prev)));
        }
        else{
        $str = amf_encode(hex2bin($file), AMF_CLASS_MAPPING);

       Storage::disk('media')->put( $name,  amf_decode($str));
        }
    }
}

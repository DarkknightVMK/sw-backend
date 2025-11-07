<?php


class camera
{
    function savePhoto($bytearray)
    {
        $amf = new stdClass();
        if(!Storage::disk('media')->exists(session('user')))
        {
            Storage::disk('media')->makeDirectory(session('user'));
        }
        $id = $this->generateRandomString() . time();
        $filename = session('user').'/'. session('user') . '_' . $id . '.jpg';
        //resize image
        
        $media_url = \Config::get('custom.media_url');


        $this->uploadFile($bytearray,   $filename);

     //TODO enable JPEG support
       
       $thumb = session('user').'/'. session('user') . '_' .$this->generateRandomString() . time() . '.png';
    //    sleep(3);
         $this->uploadFile($bytearray,   $thumb, $filename, true);

        $amf->id = $this->generateRandomString() . time();
        $amf->userId = session('user');
        $amf->avatarId = session('avatar');

        $amf->photoUrl = $media_url . '/'. $filename;
        $amf->thumbUrl = $media_url . '/'. $thumb;
        // $amf->timestamp = amf_decode(time());
        $amf->success = true;
        return $amf;
    }

    function uploadFile($file, $name,$prev = "", $thumb = false)
    {


        if ($thumb == true)
        {
            $img = Image::make( Storage::disk('media')->get($prev)); 
            $img->resize(128, 96, function ($constraint) {
                $constraint->aspectRatio();
            })->encode('png');
            Storage::disk('media')->put( $name,  $img);

            // $img->move(str_replace(chr(0), '', Storage::disk('media')->get($prev)));
            
        }
        else{
        $str = amf_encode($file->data, AMF_CLASS_MAPPING);

       Storage::disk('media')->put( $name,  amf_decode($str));
        }
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
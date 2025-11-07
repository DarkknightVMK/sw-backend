<?php
use App\result\ServiceResult;
use App\Http\Services\functions;
use App\Models\Avatars;

use Carbon\Carbon;

class core
{
    function getMyProfile()
    {
        $amf = new stdClass();
        $amf->avatarId = strval(session('avatar'));
        $amf->hasAuthenticator = true;
        $amf->dataTexts = array();
        $amf->dataFields = array();
        $amf->joinDate = new Amfphp_Core_Amf_Types_Date( Carbon::createFromTimestamp(strtotime(functions::getUser('created_at')))->valueOf());
        $amf->success=true;
        return $amf;
    }
    function getAvatarProfile($timeconfig, $id)
    {
        $uid = Avatars::where('avatar_id', $id)->first()->owner_id;
        $amf = new stdClass();
        $amf->avatarId = strval($id);
        $amf->hasAuthenticator = true;
        $amf->dataTexts = array();
        $amf->dataFields = array();
        $amf->joinDate = new Amfphp_Core_Amf_Types_Date( Carbon::createFromTimestamp(strtotime(functions::getUserById($uid,'created_at')))->valueOf());
        $amf->success=true;
        return $amf;
    }

    function saveMyProfileChanges($timeconfig, $unk, $options)
    {
        // [{"id":"1","setValue":"hi"}] separate keys by commas
        // convert to array
        $options = json_decode($options, true);
        // loop through arra
        // var_dump($options['id']);
        foreach ($options as $option) {
            // update user
            $id = $option['id'];
            $value = $option['setValue'];
            // Users::where('id', session('user'))->update([$option['id'] => $option['setValue']]);
        }

        return new ServiceResult;
    }
}

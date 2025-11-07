<?php
use App\Models\Users;

class group
{
    function updatePrimaryGroup($timeconfig, $uid, $id)
    {
        $user = Users::where('id', $uid)->first();

        $amf = new stdClass();
        //update
        if ($id != null)
        {
            $user->primaryGroupId = $id;
            $user->save();
        }
        $amf->success=true;
        return $amf;
    }

    function updateAdditionalGroups($timeconfig, $uid, $ids)
    {
        $amf = new stdClass();

        //update
        if ($ids != array())
        {
            $user = Users::where('id', $uid)->first();
            $user->secondaryGroupIds = implode(',', $ids);
            // $group->permissionId = implode(", ", $pid);
            $user->save();


        }
        else if ($ids == array())
        {
            $user = Users::where('id', $uid)->first();
            $user->secondaryGroupIds = null;
            $user->save();
        }
        $amf->success=true;
        return $amf;
    }
}

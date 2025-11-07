<?php
use App\Models\userGroups;


class group
{
    function searchGroups($timeconfig, $id, $name, $cuid, $muid, $active)
    {
        //cuid = Creator User id, muid = Member user id, id = group id
        $amf = new stdClass();
        $group = userGroups::where('id', $id)->get();
        $group2 = str_replace('["', '', $group);
        $group3 = str_replace('"]', '', $group2);
        $pid = explode(', ', strval($group3));
        $arr = array();
        for ($i = 0; $i < count($group); $i++)
        {
//                if ($permissions[$k]['catId'] != $cat[$i]['id'] && $type != 'user')
//                    continue;
//                $key = array_diff_key($key, [0]);
            $arr[] = array
            (
                'id' => $group[$i]['id'],
                'active' => $group[$i]['active'],
            );
        }
        $amf->recordSet = $arr;
        $amf->success=true;
        return $amf;
    }
    function getGroupDetails($timeconfig, $id)
    {
        //cuid = Creator User id, muid = Member user id, id = group id
        $amf = new stdClass();

        $amf->id = $id;
        $amf->success=true;
        return $amf;
    }
}

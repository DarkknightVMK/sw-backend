<?php
use App\Models\userGroups;

class groups
{
    function getUserGroups()
    {
        $amf = new stdClass();
        $arr = array();
        $groups = userGroups::whereIn('type', ['A','B'] )->get();
        for ($i = 0; $i < count($groups); $i++)
        {
            $arr[] = array
            (
                'id' => $groups[$i]['id'],
                'name' => $groups[$i]['name'],
                'description' => $groups[$i]['description']
            );
        }
        $amf->recordSet = $arr;
        $amf->success=true;
        return $amf;
    }
    function getSystemUserGroups()
    {
        $sgroup = userGroups::where('type', "A")->get();
        $arr = array();
        for ($i = 0; $i < count($sgroup); $i++)
        {
            $arr[] = array
            (
                'id' => $sgroup[$i]['id'],
                'name' => $sgroup[$i]['name'],
                'description' => $sgroup[$i]['description']
            );
        }
        $amf = new stdClass();
        $amf->recordSet = $arr;
        $amf->success=true;
        return $amf;
    }
    function getBanUserGroups()
    {
        $bgroup = userGroups::where('type', 'B')->get();
        $arr = array();
        for ($i = 0; $i < count($bgroup); $i++)
        {
            $arr[] = array
            (
                'id' => $bgroup[$i]['id'],
                'name' => $bgroup[$i]['name'],
                'description' => $bgroup[$i]['description']
            );
        }
        $amf = new stdClass();
        $amf->recordSet = $arr;
        $amf->success=true;
        return $amf;
    
    }

    function getFraudUserGroups()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }

    function getCustomUserGroups()
    {
        $amf = new stdClass();
        $amf->recordSet[] = array
        (
            'id' => "2",
            'name' => "custom",
            'description' => "a group of testers moo",
        );
        $amf->success=true;
        return $amf;
    }
    function addGroup($timeconfig, $groupName)
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

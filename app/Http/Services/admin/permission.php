<?php
use App\Models\permissionCategories;
use App\Models\userPermissions;
use App\Models\userGroups;

class permission
{
    function getProductPermissions($timeconfig, $id): stdClass
    { //id = 1 = SmallWorlds Permissions! id = 2 = SMI Permissions!
        $amf = new stdClass();
        $type = 'product';
        $amf->recordSet = $this->getPermissions($type, $id);
        $amf->success=true;
        return $amf;
    }
    function getPermissionsForUserGroup($timeconfig, $id): stdClass
    {
        $amf = new stdClass();
        $type = 'user';
        $amf->recordSet = $this->getPermissions($type, $id);
        $amf->success=true;
        return $amf;
    }
    function savePermissions($timeconfig, $gid, $pid)
    {
        $group = userGroups::find($gid);
//        $permission = new userGroups();
//        $permission = userGroups::where
        $group->permissionId = implode(", ", $pid);
        $group->save();
//        $group->userPermission()->save($permission);
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }

    private function getPermissions($type, $id): array
    {
        if ($type == 'user')
        {
            $group = userGroups::where('id', $id)->pluck('permissionId');
            $group2 = str_replace('["', '', $group);
            $group3 = str_replace('"]', '', $group2);
            $pid = explode(', ', strval($group3));
            if($pid[0] == "[null]")
                return array();
            $permissions = userPermissions::whereIn('id', $pid )->get()->toArray();
            $cat = permissionCategories::where('id', $permissions[0]['catId'])->get();
            $arr = array();
            for ($k = 0; $k < count($permissions); $k++)
            {
//                if ($permissions[$k]['catId'] != $cat[$i]['id'] && $type != 'user')
//                    continue;
                $cate = permissionCategories::where('id', $permissions[$k]['catId'])->get();
//                $key = array_diff_key($key, [0]);
                $arr[] = array
                (
                    'category' => array
                    (
                        'name' => $cate[0]['name'],
                        'id' => $cate[0]['id'],
                        'product' => $cate[0]['product'],
                        'orderIndex' => $cate[0]['orderIndex'],
                    ),
                    'name' => $permissions[$k]['name'],
                    'id' => $permissions[$k]['id'],
                    'desc' => $permissions[$k]['desc'],
                    'sortOrder' => $permissions[$k]['sortOrder'],
//                    ''.$k[$i].'' => $v[$i],
                );
            }
            return $arr;
//            var_dump(count($permissions));
        }
        else {
            $permissions = userPermissions::all();
            $cat = permissionCategories::where('product', $id)->get();

            $count = count($cat);
            $countP = count($permissions);
//        var_dump($count);
            $result = "";
            $arr = array();
            for ($i = 0; $i < $count; $i++) {
//        foreach ($cat as $key => $value)
//        {
//            var_dump($key);
//            foreach($permissions as $k => $v)
//            {

                for ($k = 0; $k < $countP; $k++) {
                    if ($permissions[$k]['catId'] != $cat[$i]['id'] && $type != 'user')
                        continue;
//                    $cate = permissionCategories::where('id', $permissions[$k]['catId'])->get();
//                $key = array_diff_key($key, [0]);
                    $arr[] = array
                    (
                        'category' => array
                        (
                            'name' => $cat[$i]['name'],
                            'id' => $cat[$i]['id'],
                            'product' => $cat[$i]['product'],
                            'orderIndex' => $cat[$i]['orderIndex'],
                        ),
                        'name' => $permissions[$k]['name'],
                        'id' => $permissions[$k]['id'],
                        'desc' => $permissions[$k]['desc'],
                        'sortOrder' => $permissions[$k]['sortOrder'],
//                    ''.$k[$i].'' => $v[$i],
                    );
                }
//                $arr[] = array (  );
//                $result .= "(".$k.": ".$v.") ";
            }
            return $arr;
        }
    }
}

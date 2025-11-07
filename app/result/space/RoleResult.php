<?php
 namespace App\result\space;
 use App\result\ServiceResult;
 use App\Models\spaceMember;
 use App\Models\spaceRoles;
 use App\Models\avatarSpaces;
 use App\Models\userGroups;
 use App\Http\Services\functions;
 use App\result\UserPermissions;

 #[\AllowDynamicProperties]
 class RoleResult extends ServiceResult
 {
    public $_explicitType = 'com.smallworlds.entity.space.result.RoleResult';

     function __construct($space)
    {
        $owner = avatarSpaces::where('id', $space->id)->where('user_id', session('user'))->exists();
        $spaceMember = spaceMember::where('space_id', $space->id)->where('avatar_id', session('avatar'))->first();
        if ($spaceMember != null)
        $spaceRole = spaceRoles::where('id', $spaceMember->spacerole_id)->first();
        // if (session('user') == $space->user_id)
        //     $this->role = 'owner';
        // else
        //     $this->role = 'guest';
        $this->roleDesc = ($spaceMember != null) ? $spaceRole->desc : (($owner) ? "Owner" : "Guest");
        $this->spaceAccessControl = $space->accessControl;
        $this->officer = ($spaceMember != null) ? $this->toBool($spaceRole->officer_flag) : (($owner) ? true :  false);
        $this->edit = ($spaceMember != null) ? $this->toBool(strval($spaceRole->edit_flag)) : (($owner) ? true : false);
        $this->isMember = ($spaceMember != null) ? true : false;
        $primaryGroup = functions::getUser('primaryGroupId');
        $group = userGroups::where('id', $primaryGroup)->pluck('permissionId');
        $group2 = str_replace('["', '', $group);
        $group3 = str_replace('"]', '', $group2);                 
        $pid = explode(', ', strval($group3));
        if (in_array(UserPermissions::SPACE_ACCESS_ANY, $pid))
            $this->access = true;
        else
            $this->access = null;

        if ($this->access == null && $space->accessControl == 'M' && $spaceMember == null && !$owner)
            $this->access = false;
        else if ($this->access == null && $spaceMember != null)
            $this->access = $this->toBool($spaceRole->access_flag);
        else if ($this->access == null )
            $this->access = true;
        $this->isShowroom = false;

        parent::__construct(true);
        return $this;
    }

    function toBool($var) {
        if (!is_string($var)) return (bool) $var;
        switch (strtolower($var)) {
          case "y":
            return true;
          default:
            return false;
        }
    }

 }
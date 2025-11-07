<?php
 namespace App\result\space;

 use App\Http\Services\functions;
 use App\result\UserPermissions;

 #[\AllowDynamicProperties]
 class SpaceBanInfo 
 {
    public $_explicitType = 'com.smallworlds.entity.space.model.SpaceBanInfo';

    public function __construct($ban)
    {
      $this->id = strval($ban->id);
      $this->avatarFName = functions::getAvatarByID($ban->avatar_id, 'firstName');
      $this->avatarLName = functions::getAvatarByID($ban->avatar_id, 'lastName');
      $this->avatarNameInstance = (float)functions::getAvatarByID($ban->avatar_id, 'nameInstance');
      return $this;
    }
 }
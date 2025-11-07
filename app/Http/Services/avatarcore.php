<?php
use App\result\ServiceResult;
use App\result\StringResult;
use App\result\BooleanResult;
use App\Models\Avatars;
use App\result\CompressionUtil;

class avatarcore
{
    public $avatar;
    function createAvatar($timeconfig, $fName, $lName, $gender)
    {
      $ret = new stdClass();
        $ret->success = true;
        return $ret;
    }
// TODO
    function getAvatarVip($timeconfig,$aid)
    {
      return new BooleanResult(true);
    }
    function getAvatarFoundingMember($timeconfig,$aid)
    {
      return new BooleanResult(true);
    }
    function getAvatarGender($timeconfig,$aid)
    {
      $avi = Avatars::find($aid);
      if ($avi->gender == "F")
        $gender = 'female';
      else
        $gender = 'male';
      return new StringResult($gender);
    }

}

<?php
use App\result\UserCPBalanceResult;
use App\result\UserCPSettingsResult;
use App\result\ServiceResult;
use App\Models\Avatars;
use App\Models\Users;
class user
{
  function getAvatarCPBalance($timeconfig, $aid)

  {
    $avi = Avatars::find($aid);
    $user = Users::find($avi->owner_id);
    return new UserCPBalanceResult($user);
  }
  function getAvatarCPSettings($timeconfig, $aid)
  {
    $avi = Avatars::find($aid);
    return new UserCPSettingsResult($avi);
  }

  function getMyCPBalance()
  {
    $user = Auth::user();
      return new UserCPBalanceResult($user);
  }
}
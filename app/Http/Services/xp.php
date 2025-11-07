<?php
use App\result\ServiceResult;
use App\result\xp\AvatarXP;
use App\result\ListResult;
use App\Models\avatar_xp;
class xp
{
  function findSkillsByAvatar($timeconfig, $aid)
  {
   //xpType 1-6 not 8
    return new ListResult(  
      json_decode(avatar_xp::where('avatar_id', $aid)
      ->where('xpleveltype', '<>', 8)
        ->get()
        ->map(function ($xp) {
          return new AvatarXP($xp);
        }))
    );
  }
}
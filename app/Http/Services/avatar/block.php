<?php 
use App\Models\avatarBlocks;
use App\result\BooleanResult;

class block
{
  function isAvatarBlocked($timeconfig, $avatarId)
  {
    $blocked = avatarBlocks::where('avatar_id', session('avatar'))->where('block_id', $avatarId)->exists();
    return new BooleanResult($blocked);

  }
}
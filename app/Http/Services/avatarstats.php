<?php
use App\Models\onlineUsers;
class avatarstats
{
  function getOnlineAvatarsCount()
  {

    $amf = new stdClass();
    $amf->data = (float) onlineUsers::where('online',true)->count();
    $amf->success=true;
    return $amf;
  }
}
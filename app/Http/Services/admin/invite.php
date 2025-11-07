<?php
class invite
{
  function getInvitedUserDetails($timconfig, $aid)
  {
    $amf = new stdClass();
    $amf->success=true;
    return $amf;

  }
}
<?php
use App\result\ServiceResult;
use App\result\BooleanResult;

class authenticator
{
  function getAvatarAuthenticatorStatus($timeconfig, $aid)
  {
    return new BooleanResult(true);
  }
}
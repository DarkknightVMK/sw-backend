<?php
use App\result\BooleanResult;

class mule
{
  function canGiftItem($timeconfig, $uid)
  {
   return new BooleanResult(true);
  }
}

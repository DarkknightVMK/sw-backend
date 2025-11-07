<?php

class allocation
{
  function getAllocationsForSpace($timeconfig, $spaceID)
  {
    $amf = new stdClass();
    $amf->success=true;
    return $amf;
  }
}
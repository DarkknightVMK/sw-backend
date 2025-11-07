<?php

class avatarsubtask
{
  function getSubtasks($timeconfig, $mid)
  {
    $amf = new stdClass();
    $amf->list = array();
    $amf->success=true;
    return $amf;
  }
}
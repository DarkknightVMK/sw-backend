<?php
class facebook 
{
  public function checkPermissions($array)
  {
    $amf = new stdClass();
    $amf->success = true;
    return $amf;
  }
}
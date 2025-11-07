<?php
// include ('../functions.php');
// use App\Http\Services\functions;
use App\Models\avatarSpaces;

  class schedule
  {

    public function saveSpaceSchedulingDetails($timeconfig, $spaceID, $start, $end, $repeat )
    {
      $amf = new stdClass();
      $amf->success = true;
      return $amf;
    }

    function saveSpaceSwappingDetails($timeconfig, $spaceId, $swapSpaceId, $date, $repeat)
    {
      $amf = new stdClass();
      $amf->success = true;
      return $amf;
    }
  }

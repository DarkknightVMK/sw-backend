<?php
// include ('../functions.php');
// use App\Http\Services\functions;
use App\Models\avatarSpaces;

  class shop
  {
    public function saveSpaceShopDetails($timeconfig, $spaceID, $type, $displayOrder )
    {
      $amf = new stdClass();
      $amf->success = true;
      return $amf;
    }
  }

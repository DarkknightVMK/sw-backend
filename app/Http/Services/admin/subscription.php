<?php

class subscription
{
  function getUserRevenueDetails($timconfig, $aid)
  {
    $amf = new stdClass();
    $amf->numPurchases = 5;
    // $amf->vipExpiryDate = "";
    $amf->hasPurchased = true;
    $amf->isVIP = true;
    // $amf->lastPurchaseDate = "";
    $amf->id = $aid;
    $amf->dollars = 120;
    $amf->success=true;
    return $amf;

  }
}
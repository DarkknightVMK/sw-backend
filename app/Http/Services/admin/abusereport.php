<?php

class abusereport
{
  function getUserReportDetails($timconfig, $aid)
  {
    $amf = new stdClass();
    $amf->success=true;
    return $amf;

  }
  function getUnreadAbuseReportCounts()
  {
    $amf = new stdClass();
    $amf->data = 0;
    $amf->success=true;
    return $amf;
  }
}
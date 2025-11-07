<?php
class filter {
  function filter ( ) {

  }
  function getActiveFilters() {
    
      $ret = new stdClass();
      $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
      $ret->$explicitTypeField = "com.smallworlds.communication.filter.external.amf.data.FilterListResult";
      $ret->success = true;
      $ret->filters = array();
      return $ret;
  }
}

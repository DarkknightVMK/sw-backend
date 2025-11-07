<?php


class channel
{

    function viewMyChannels()
    {       
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";
        $ret->recordSet = array();
        $ret->startIndex = 0;
        $ret->maxLength = 50;
        $ret->totalCount = 0;
        $ret->success= true;
      return $ret;
    }
}
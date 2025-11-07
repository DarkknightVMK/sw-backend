<?php


class brag
{
    function getMyBrags()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.UntypedRecordSetResult";
        $amf->startIndex = 0;
        $amf->maxLength = 0;
        $amf->totalCount = 0;
        $amf->recordSet = array();
        $amf->success=true;
        return $amf;
    }
}

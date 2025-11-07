<?php


class loadtime
{
    function trackLoadTime($param, $param2, $param3,$param4,$param5)
    {
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.service.result.ServiceResult";
        $ret->success=true;
        return $ret;
    }
}
<?php


class log
{
    function viewMyXPLogs($timeconfig,$param, $days)
    {
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->success = true;
        return $ret;
    }
}

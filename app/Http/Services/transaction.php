<?php


class transaction
{
    function getMyTransactions($timeconfig, $start, $end)
    {
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->success = true;
        return $ret;
    }
}

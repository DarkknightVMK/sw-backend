<?php


class interstitial
{

    function getValidInterstitials()
    {
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.service.result.ListResult";
        $ret->list = array();
        $ret->success = true;
        return $ret;
    }
}
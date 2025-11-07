<?php


class paymentmethod
{
    function getAllIntegrations()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

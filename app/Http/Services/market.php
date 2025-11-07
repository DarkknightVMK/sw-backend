<?php


class market
{
    function getMySellOrders()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

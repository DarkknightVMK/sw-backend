<?php


class currency
{
    function getSearchableCurrencies()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

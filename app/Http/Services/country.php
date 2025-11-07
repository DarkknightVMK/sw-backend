<?php


class country
{
    function getAllCountries()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

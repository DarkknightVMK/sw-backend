<?php


class integration
{
    function getIntegratedGames()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

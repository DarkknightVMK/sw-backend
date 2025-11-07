<?php


class scheduling
{
    function getCurrentContent()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
    function getUpcomingContent()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

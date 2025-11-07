<?php


class genre
{
    function getAllGenres()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

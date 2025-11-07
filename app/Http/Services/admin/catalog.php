<?php


class catalog
{
    function getAllCategories()
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

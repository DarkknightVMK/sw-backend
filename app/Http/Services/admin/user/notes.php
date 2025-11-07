<?php

class notes
{
    function saveUserNotes($timeconfig, $uid, $note)
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

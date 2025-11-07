<?php

use App\result\ServiceResult;

class conversation
{
    function getMyConversations()
    {
        $amf = new stdClass();
        $amf->list = array();
        $amf->success=true;
        return $amf;
    }
    function getPendingMessages()
    {
        $amf = new stdClass();
        $amf->list = array();
        $amf->success=true;
        return $amf;
    }

    function createConversation($timeconfig, $aid, $param)
    {
        return new ServiceResult(true);
    }
}

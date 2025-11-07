<?php

use App\Models\missions;

class graph
{
    function getAllMissionNodes()
    {
        $amf = new stdClass();
        $missionChains = missions::all();
        if ($missionChains->isEmpty()) {
            $amf->recordSet = [];
            $amf->startIndex = 0;
            $amf->maxLength = 0;
            $amf->totalCount = 0;
            $amf->success = true;
            return $amf;
        }
        foreach ($missionChains as $missionChain)
        {
            $arr[] = array (
                'id' => strval($missionChain->id),
                'title' => $missionChain->title,
                'active' => $missionChain->active,
                'orderNumber' => 1, //TODO add order number to DB
                'missionTitle' => $missionChain->title,
            );
        }
        $amf->recordSet = $arr;
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }

    function getMissionGraphTree($bool)
    {
        $amf = new stdClass();
        $missionChains = missions::all();
        foreach ($missionChains as $missionChain)
        {
            $arr[] = array (
                'id' => strval($missionChain->id),
                'title' => $missionChain->title,
                'active' => $missionChain->active,
                'orderNumber' => 1, //TODO add order number to DB
                'missionTitle' => $missionChain->title,
            );
        }
        $amf->recordSet = $arr;
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }
}

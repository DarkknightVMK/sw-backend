<?php

use App\Models\missions;
use App\Models\missionTasks;
use App\Models\avatarMissions;

class task
{
    function searchTasks($timeconfig,$mid,$tid)
    {
        $amf = new stdClass();
        // $fnc = new functions();
        $missionChain = missions::where('id', $mid)->get()->first();
        $missionTask = missionTasks::where('missionId', $missionChain->id)->get();
        $arr = array();
        foreach ($missionTask as $task)
        {
            $arr[] = array (
                'id' => strval($task->id),
                'missionTitle' => $missionChain->title,
                'title' => $task->title,
                'missionId' => $task->missionId,
                'active' => $task->active,
                'orderNumber' => $task->orderNumber, //TODO add order number to DB

            );
        }
        $amf->recordSet = $arr;
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }
    function saveTask($timeconfig, $tId, $title, $desc, $completedDesc, $script, $active)
    {
        $amf = new stdClass();
        $missionTask = missionTasks::where('id', $tId)->get()->first();
        $missionTask->title = $title;
        $missionTask->desc= $desc;
        $missionTask->completedDesc = $completedDesc;
        $missionTask->script = $script;
        $missionTask->active = $active;
        $missionTask->save();
        $amf->success=true;
        return $amf;
    }
    function getTaskDetails($timeconfig, $tid)
    {
        $amf = new stdClass();
        $missionTask = missionTasks::where('id', $tid)->get()->first();
        $mission = missions::where('id', $missionTask->missionId)->get()->first();
        $amf->active = $missionTask->active;
        $amf->title = $missionTask->title;
        $amf->desc = $missionTask->desc;
        $amf->id = $missionTask->id;
        $amf->script = $missionTask->script;
        $amf->completedDesc = $missionTask->completedDesc;
        $amf->missionId = $missionTask->missionId;
        $amf->missionTitle = $mission->title;
        $amf->success = true;
        return $amf;
    }
}

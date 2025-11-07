<?php


class conditionrule
{
    function getAllActiveConditionRules()
    {
        $amf = new stdClass();
        $arr[] = array(
            'id' => '1',
            'active' => true,
            'desc' => 'Condition Rule 1',
            'inUse' => true,
            'key' => 'conditionrule1',
            'script' => '12345',
        );
        $amf->recordSet = $arr;
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }

    function searchConditionRules($bool)
    {
        $amf = new stdClass();
        $arr[] = array(
            'id' => '1',
            'active' => true,
            'desc' => 'Condition Rule 1',
            'inUse' => true,
            'key' => 'conditionrule1',
            'script' => '12345',
        );
        $amf->recordSet = $arr;
        $amf->startIndex = 0;
        $amf->maxLength = 50;
        $amf->totalCount = 0;
        $amf->success=true;
        return $amf;
    }

    function getConditionRuleDetails($timeconfig, $id)
    {
        $amf = new stdClass();
        $amf->active = true;
        $amf->key = 'conditionrule1';
        $amf->script = '12345';
        $amf->id = '1';
        $amf->desc = 'Condition Rule 1';
        $amf->inUse = true;
        $amf->version = 1;
        $amf->details = "Some deets";
        $amf->success=true;
        return $amf;
    }
    // function addConditionRule()
    // {
    //     $amf = new stdClass();
    //     $amf->active = true;
    //     $amf->key = 'conditionrule1';
    //     $amf->script = '12345';
    //     $amf->id = '1';
    //     $amf->desc = 'Condition Rule 1';
    //     $amf->inUse = true;
    //     $amf->version = 1;
    //     $amf->details = "Some deets";
    //     $arr[] = array(
    //         'id' => '1',
    //         'active' => true,
    //         'desc' => 'Condition Rule 1',
    //         'inUse' => true,
    //         'key' => 'conditionrule1',
    //         'script' => '12345',
    //     );
    //     $amf->recordSet = $arr;
    //     $amf->startIndex = 0;
    //     $amf->maxLength = 50;
    //     $amf->totalCount = 0;
    //     $amf->success=true;
    //     return $amf;
    // }
}

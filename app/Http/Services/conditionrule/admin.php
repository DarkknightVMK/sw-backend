<?php
namespace conditionRule;
class admin
{
    function getAllActiveConditionRules()
    {
        $ret = new stdClass();
        $ret->success = true;
        return $ret;
    }
}
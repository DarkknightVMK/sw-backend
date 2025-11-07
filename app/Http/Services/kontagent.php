<?php


class kontagent
{

    function  getCurrentApp()
    {
        $param1 = new stdClass();


        $param1->success = true;
        return $param1;
    }

    function queueTPC($p, $p2, $p3, $p4, $p5, $p6)
    {
        $param1 = new stdClass();

        $param1->apiKey = Null;
        $param1->testMode = true;
        $param1->appId = "";
        $param1->responseLandingBaseURL = "";
        $param1->success = true;
        return $param1;
    }
}
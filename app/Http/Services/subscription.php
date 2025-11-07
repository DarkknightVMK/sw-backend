<?php


//namespace App\Http\Services;


class subscription
{

    function getMySubscriptionStatus()
    {
        $amf= new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        // $ret->recurringDate = "";
        // Carbon + 1 year 
        $amf->data = array(
            // 'recurringDate' => "",
            'status' => 4,
            'endDate' => new Amfphp_Core_Amf_Types_Date(Carbon\Carbon::now()->addYear()->valueOf()),
            'startDate' => new Amfphp_Core_Amf_Types_Date(Carbon\Carbon::now()->valueOf()),
            'isRecurring' => false,
            'id' => 1,
        );
        $amf->$explicitTypeField = "com.smallworlds.service.result.SubscriptionStatusResult";
        // $ret->recurringDate = ;
        // $ret->vipSince = "";
        // $ret->endDate = "";
        // $ret->status = 4;
        // $ret->isRecurring = true;
        // $ret->id = 1;
        // $ret->startDate = new Amfphp_Core_Amf_Types_Date(Carbon\Carbon::now()->valueOf());
        // $ret->success = true;

        return $amf;
    }
    function getPurchasedBundlesForUser($timeconfig, $uid)
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.ListResult";
        $amf->list = array();
        $amf->success=true;
        return $amf;
    }
}

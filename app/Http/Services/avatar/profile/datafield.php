<?php


class datafield
{
    function getOrderedDataFields()
    {
        $amf = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $amf->$explicitTypeField = "com.smallworlds.service.result.ListResult";
        $arr2[] = array('id' => '1', 'desc' => 'Single', 'color' => 'B6D7A8', 'iconUrl' => null, 'success' => true);
        $arr[] = array('id' => '1',
        'desc'=> 'Social Status',
        'tooltipSelf' => 'What is your current social status?',
        'defaultIconUrl' => 'avatars/profile/icons/relationship.png',
        'success' => true,
        'tooltipOthers' => "{avatarFName}'s current social status",
        'options' => array(array('id' => '1', 'desc' => 'Single', 'color' => 'B6D7A8', 'iconUrl' => null, 'success' => true)),
    );
        $amf->list = $arr;
        $amf->success=true;
        return $amf;
    }
}

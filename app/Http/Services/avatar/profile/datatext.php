<?php


class datatext
{
    public function getOrderedDataTexts()
    {
        $amf = new stdClass();
        $amf->list = array($this->arr("1", "About Me") , $this->arr("2", "Contact me if..."));
        $amf->success=true;
        return $amf;
    }

    public function arr(string $id, string $desc): stdClass
    {
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.entity.avatar.profile.datatext.external.amf.result.DataTextResult";
        $ret->id = $id;
        $ret->desc = $desc;
        $ret->maxChars = 2000;
        $ret->success = true;

        return $ret;
    }

}

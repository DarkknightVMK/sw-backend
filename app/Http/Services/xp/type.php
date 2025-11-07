<?php


class type
{
    function getAllXPTypes()
    {
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.communication.filter.external.amf.data.FilterListResult";
        $ret->recordSet = array($this->XPType(1,"artist levels"), $this->XPType(2,"explorer levels"), $this->XPType(3,"gamer levels"), $this->XPType(4,"social levels"),
        $this->XPType(5,"arena levels"), $this->XPType(6, "farmer levels"), $this->XPType(7,"crafting levels"), $this->XPType(8, "primary level", null, "xplevel/primary_xplevel_script.as"));
        $ret->success = true;
        return $ret;
    }

    function XPType($id, $desc, $dailyCap = 1000, $script = null)
    {
        $ret = new stdClass();
        $explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
        $ret->$explicitTypeField = "com.smallworlds.entity.avatar.xp.type.dao.data.XPType";
        $ret->id = strval($id);
        $ret->desc = $desc;
        $ret->dailyCap = (float)$dailyCap;
        $ret->levelCap = (float)499;
        $ret->clientScriptUrl = "xplevel/smallworlds_xplevel_script.as";
        return $ret;
    }
}
<?php

class infraction
{
    function getUserInfractionDetails($timconfig, $aid)
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }

    function searchInfractions($timconfig, $unk,$unk1,$unk2,$unk3,$unk4,$unk5,$unk6,$unk7,$unk8,$unk9,$unk10)
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
    function searchRecentEscalations($timconfig, $aid,$unk)
    {
        $amf = new stdClass();
        $amf->success=true;
        return $amf;
    }
}

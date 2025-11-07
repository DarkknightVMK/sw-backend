<?php

class invite
{
    public function getCurrentInviteInfo()
    {
        $amf = new stdClass();
        $amf->rankUpCL = 5;
        $amf->numInvited = 0;
        $amf->inviteURL = 'https://?????/invite/???/';
        $amf->inviteInfoURL = 'https://????/invite/???/';
        $amf->rewardCP = 200;
        $amf->rewardGold = 500;
        $amf->rewardTokens = 5000;
        $amf->rankUpInvites = 5;
        $amf->rankUpCLName = 'God';
        return $amf;
    }
}
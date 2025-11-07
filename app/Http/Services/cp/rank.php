<?php




class rank
{
    function getCPRankInfo()
    {
        $amf = new stdClass();
        $amf->list = array(
            $this->getRanks('99CCFF', 'Commoner', 'ui/cp/ranks/royal/1_small.png', 1),
            $this->getRanks('EEAAFF', 'Recruit', 'ui/cp/ranks/royal/2_small.png', 2, 'ui/cp/ranks/royal/2_large.png'),
            $this->getRanks('EEAAFF', 'Citizen', 'ui/cp/ranks/royal/3_small.png', 5, 'ui/cp/ranks/royal/3_large.png'),
            $this->getRanks('EEAAFF', 'Officer', 'ui/cp/ranks/royal/4_small.png', 10, 'ui/cp/ranks/royal/4_large.png'),
            $this->getRanks('EEAAFF', 'Knight,Dame', 'ui/cp/ranks/royal/5_small.png', 20, 'ui/cp/ranks/royal/5_large_male.png,ui/cp/ranks/royal/5_large_female.png'),
            $this->getRanks('EEAAFF', 'Baron,Baroness', 'ui/cp/ranks/royal/6_small.png', 40, 'ui/cp/ranks/royal/6_large_male.png,ui/cp/ranks/royal/6_large_female.png'),
            $this->getRanks('EEAAFF', 'Count,Countess', 'ui/cp/ranks/royal/7_small.png', 80, 'ui/cp/ranks/royal/7_large_male.png,ui/cp/ranks/royal/7_large_female.png'),
            $this->getRanks('FCDB83', 'Duke,Duchess', 'ui/cp/ranks/royal/8_small.png', 160, 'ui/cp/ranks/royal/8_large_male.png,ui/cp/ranks/royal/8_large_female.png'),
            $this->getRanks('FCDB83', 'Arch Duke,Arch Duchess', 'ui/cp/ranks/royal/9_small.png', 240, 'ui/cp/ranks/royal/9_large_male.png,ui/cp/ranks/royal/9_large_female.png'),
            $this->getRanks('FCDB83', 'Grand Duke,Grand Duchess', 'ui/cp/ranks/royal/10_small.png', 320, 'ui/cp/ranks/royal/10_large_male.png,ui/cp/ranks/royal/10_large_female.png'),        
        );
        $amf->success=true;
        return $amf;
    }

    function getRanks($color, $name, $smallIcon, $minCL, $large = null)
    {
        $amf = new stdClass();
        // $amf->$explicitTypeField = "com.smallworlds.entity.avatar.xp.type.dao.data.XPType";
        $amf->color = $color;
        $amf->canOverride = true;
        $amf->name = $name;
        $amf->themeName = 'Royal';
        $amf->themeKey = 'royal';
        $amf->smallIconPath = $smallIcon;
        $amf->minCitizenLevel = $minCL;
        $amf->largeIconPath = $large;
        $amf->success = true;
        return $amf;
    }
}

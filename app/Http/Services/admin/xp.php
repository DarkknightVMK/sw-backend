<?php
use App\result\ServiceResult;
use App\Models\avatar_xp;
use App\Models\Users;
use App\Models\onlineUsers;

class xp
{

  public function editXPLevel($timeconfig, $aid, $xpleveltype, $level)
  {
		$user = Users::find(session('user'));
		if ($user->serverIP != '127.0.0.1')
		$client = new SabreAMF_Client("https://".SITE_DOMAIN."/java/swds/gateway;jsessionid=".session('id')); // Set up the client object
	else
		$client = new SabreAMF_Client("https://".SITE_DOMAIN."/localhost/swds/gateway;jsessionid=".session('id'));
		if ($level >=500 && $level <= 700)
		 $level = 700;
		if ($level >= 701 && $level <= 899)
		 $level = 800;
		if ($level >= 900 && $level <= 999)
		 $level = 900;
		if ($xpleveltype == 6 && $level >= 300)
		 $level = 300;
		if ($xpleveltype == 7 && $level >= 200)
		 $level = 200;
		avatar_xp::where('avatar_id', $aid)->where('xpleveltype', $xpleveltype)->update(['level' => $level, 'xp' => $this->calcXP($level, $xpleveltype)]);
		$primaryLevel = avatar_xp::where('avatar_id', $aid)->where('xpleveltype', 8)->first()->level;
		//update primary
		$primaryXp =     avatar_xp::where('avatar_id', $aid)->where('xpleveltype', '!=', 8)->sum('xp');
		if ($primaryLevel + $this->levelUpLevels($primaryLevel, 8, $primaryXp) <= 499)
			avatar_xp::where('avatar_id', $aid)->where('xpleveltype', 8)->update(['level' => $primaryLevel  + $this->levelUpLevels($primaryLevel, 8, $primaryXp), 'xp' => $primaryXp]);
		else
			avatar_xp::where('avatar_id', $aid)->where('xpleveltype', 8)->update(['level' => 499, 'xp' => $primaryXp]);
		  //only send this if user is online
			if (onlineUsers::where('avatar_id', $aid)->where('online', true)->count() > 0)
				$client->sendRequest('ds.editXPLevel', array($aid));  

    $amf = new stdClass();
    $amf->id = $xpleveltype;
    $amf->xp = (float) $this->calcXP($level, $xpleveltype); // figure out
    $amf->level = $level;
    $amf->primaryLevel = (float) avatar_xp::where('avatar_id', $aid)->where('xpleveltype', 8)->first()->level; // figure out
    $amf->primaryXp =(float) avatar_xp::where('avatar_id', $aid)->where('xpleveltype', '!=', 8)->sum('xp'); // figure out
    $amf->success = true;
    return $amf;
  }

  public function recalculatePrimaryXP($timeconfig, $aid)
  {
		$primaryXp =     avatar_xp::where('avatar_id', $aid)->where('xpleveltype', '!=', 8)->sum('xp');
		if ((1 + $this->levelUpLevels(1, 8, $primaryXp)) <= 499 )
			avatar_xp::where('avatar_id', $aid)->where('xpleveltype', 8)->update(['level' => 1  + $this->levelUpLevels(1, 8, $primaryXp), 'xp' => $primaryXp]);
		else
			avatar_xp::where('avatar_id', $aid)->where('xpleveltype', 8)->update(['level' => 499, 'xp' => $primaryXp]);
    $amf = new stdClass();
    $amf->xp = (float) // get all xp and add it up for this avatar not including xpleveltype = 8
    avatar_xp::where('avatar_id', $aid)->where('xpleveltype', '!=', 8)->sum('xp');
    $amf->level = (float) avatar_xp::where('avatar_id', $aid)->where('xpleveltype', 8)->first()->level ; // figure out
    $amf->success = true;
    return $amf;
  }

  public function calcXP($level, $levelType)
	{
		switch ($levelType) {
			case 1: case 2: case 3: case 4: case 5: {
				if ($level < 10)
					return $level > 1 ? $level * 60 : 0;
				if ($level < 20) {
					return 600 + ($level - 10) * 60;
				}
				if ($level < 30) {
					return 1200 + ($level - 20) * 120;
				}
				if ($level < 40) {
					return 2400 + ($level - 30) * 240;
				}
				if ($level < 50) {
					return 4800 + ($level - 40) * 480;
				}
				if ($level <= 500) {
					return 9600 + ($level - 50) * 480;
				}
				if ($level == 700) {
					return 100000000;
				}
				if ($level == 800) {
					return 200000000;
				}
				if ($level == 900) {
					return 300000000;
				}
			}
			case 6: case 7:{ return (float)(floor(4 * pow($level - 1,2)));}
			case 8 : {
				return (float) (round(0.1 * 4 * pow($level - 1, 2.25)) * 10 + 60 * ($level - 1));
			}
		}



		return 0;
	}
	public function levelUpLevels($level, $levelType, $xp) : int
    {
        // based upon how much xp you have, how many levels do you gain?
        $levels = -1;
        $levelInt = $level;
        switch ($levelType)
        {
            case 1: case 2: case 3: case 4: {
//                xp = xp + calcXP(levelInt, levelType);
                $levels = 0;
                while ($xp >= $this->calcXP($levelInt + 1, $levelType)) {
                    $levelInt++;
                    $levels++;
                }
            }
            case 8: {
//                xp = xp + calcXP(level, levelType);
                while ($xp >= $this->calcXP($levelInt, $levelType)) {
                    $levelInt++;
                    $levels++;
                }
            }
        }
        return $levels;
    }

}
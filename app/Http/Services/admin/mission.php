<?php
use App\Models\missions;
use App\Models\missionTasks;
use App\Models\avatarMissions;
include ('functions.php');

class mission
{

  function searchMissions($timeconfig, $missionID, $missionTitle,$creatorUID,$creatorAID,$featuredOnly,$lastActivatedSpaceID, $xpType,$active )
  {
    // if limit if true show number
        $fnc = new functions();
        $amf = new stdClass();

        if ($missionID != null && $missionTitle == null && $creatorUID == null && $creatorAID == null && $featuredOnly == null && $lastActivatedSpaceID == null && $xpType == null)
        {
          // Search by mission ID
          $mission = missions::where('id', $missionID)->get();
        }
        elseif ($missionID == null && $missionTitle != null && $creatorUID == null && $creatorAID == null && $featuredOnly == null && $lastActivatedSpaceID == null && $xpType == null)
        {
          // Search by mission title
          $mission = missions::where('title', 'LIKE', '%'.$missionTitle.'%')->get();
        }
        elseif ($missionID == null && $missionTitle == null && $creatorUID != null && $creatorAID == null && $featuredOnly == null && $lastActivatedSpaceID == null && $xpType == null)
        {
          // Search by creator UID
          $mission = missions::where('creatorUserId', $creatorUID)->get();
        }
        elseif ($missionID == null && $missionTitle == null && $creatorUID == null && $creatorAID != null && $featuredOnly == null && $lastActivatedSpaceID == null && $xpType == null)
        {
          // Search by creator AID
          $mission = missions::where('creatorAvatarId', $creatorAID)->get();
        }
        elseif ($missionID == null && $missionTitle == null && $creatorUID == null && $creatorAID == null && $featuredOnly != null && $lastActivatedSpaceID == null && $xpType == null)
        {
          // Search by featured only
          $mission = missions::where('featuredIndex', $featuredOnly)->get();
        }
        elseif ($missionID == null && $missionTitle == null && $creatorUID == null && $creatorAID == null && $featuredOnly == null && $lastActivatedSpaceID != null && $xpType == null)
        {
          // Search by last activated space ID  
          $mission = missions::where('lastActivatedSpaceId', $lastActivatedSpaceID)->get();
        }
        elseif ($missionID != null || $missionTitle != null || $creatorUID != null || $creatorAID != null || $featuredOnly != null || $lastActivatedSpaceID != null && $xpType != null)
        {
          // Search by xp type
          $mission = missions::where('xpLevelTypeId', $xpType)->get();
        }

        if ($mission->count() > 0)
        {
          foreach($mission as $missionChain)
          {
            $tasks = missionTasks::where('missionId', $missionChain->id)->get();

            $arr[] = array(
              'id' => $missionChain->id,
              'active' => $missionChain->active,
              'timestamp' => $missionChain->timestamp,
              'title' => $missionChain->title,
              'creatorAvatarFName' => $fnc->getAvatarByID($missionChain->creatorAvatarId, 'firstName'),
              'creatorAvatarLName' => $fnc->getAvatarByID($missionChain->creatorAvatarId, 'lastName'),
              'creatorUserId' => $missionChain->creatorUserId,
              'creatorAvatarId' => $missionChain->creatorAvatarId,
              'numTasks' => count($tasks),
            );
          }

          $amf->recordSet = $arr;
          
        }
        else
        {
          $amf->recordSet = array();
        }


        $arr = array();
        // $amf->recordSet = $arr[] = array(
        //   array(
        //   'numInactiveTask' => 0,
        //   'active' => true,
        //   'creatorAvatarNameInstance' => 1,
        //   'timestamp' => null,
        //   'title' => 'Mission Title',
        //   'creatorAvatarFName' => 'Creator Avatar FName',
        //   'creatorAvatarLName' => 'Creator Avatar LName',
        //   'numTasks' => 1,
        //   'id' => 1,
        //   'creatorUserId' => 1,
        //   ),

        // );
        $amf->success=true;

        return $amf;
  }
  function findMission($timeconfig,$mid)
  {
    $amf = new stdClass();
    $mission = missions::where('id', $mid)->get()->first();
    $amf->desc = $mission->title;
    $amf->id = $mission->id;
    $amf->success=true;
    return $amf;
  }

  function saveMission($timeconfig, $missionID, $title, $desc, $completedDesc, $xpLevelTypeId, $active, $entryFeeT, $featuredIndex, $guaranteedXP, $guaranteedTokens, $guaranteedGold, $guaranteedModelId, $bonusTokens, $bonusGold, $bonusModelId, $goodMission)
  {
    $amf = new stdClass();
    $mission = missions::where('id', $missionID)->get()->first();
    $mission->title = $title;
    $mission->desc = $desc;
    $mission->completedDesc = $completedDesc;
    $mission->xpLevelTypeId = $xpLevelTypeId;
    $mission->active = $active;
    $mission->entryTokens = $entryFeeT;
    $mission->featuredIndex = $featuredIndex;
    $mission->guaranteedXP = $guaranteedXP;
    $mission->guaranteedTokens = $guaranteedTokens;
    $mission->guaranteedGold = $guaranteedGold;
    $mission->guaranteedModelId = $guaranteedModelId;
    $mission->bonusTokens = $bonusTokens;
    $mission->bonusGold = $bonusGold;
    $mission->bonusModelId = $bonusModelId;
    $mission->goodMission = $goodMission;
    $mission->save();

    $amf->success=true;
    return $amf;

  }

  function getMissionDetails($timeconfig, $mid)
  {
    $fnc = new functions();
    $amf = new stdClass();

    // $amf->guaranteedTokens = 100;
    // $amf->guaranteedGold = 100;
    // $amf->guaranteedModelId = '1344';
    // $amf->creatorUserId = 1;
    // $amf->creatorUserFirstName = 'Creator User First Name';
    // $amf->creatorUserLastName = 'Creator User Last Name';
    // $amf->creatorAvatarNameInstance = 1;
    // $amf->creatorAvatarId = 1;
    // $amf->creatorAvatarFName = 'Creator Avatar FName';
    // $amf->creatorAvatarLName = 'Creator Avatar LName';
    // $amf->bonusModelId = '1344';
    // $amf->active = true;
    // $amf->indexed = true;
    // $amf->totalPlaytime = 100;
    // $amf->timestamp = null; //DATE
    // $amf->id = $mid;
    // $amf->title = 'Mission Title';
    // $amf->desc = 'Mission Description';
    // $amf->completedDesc = 'Completed Description';
    // $amf->goodMission = true;
    // $amf->bonusGold = 100000;
    // $amf->votes = 100;
    // $amf->bonusTokens = 100;
    // $amf->xpLevelTypeId = '5';
    // $amf->reversalTime = null; //DATE
    // $amf->featuredIndex = 1;
    // $amf->creatorAvatarOnline = true;
    // $amf->totalPlaytime = 100; //seconds
    // $amf->rating = 5;
    // $amf->firstPlayed = null; // DATE
    // $amf->entryTokens = 1;
    // $amf->totalPlays =1;
    // $amf->lastActivatedSpaceId = '4';
    // $amf->spaceDesc = 'Space Description';
    // $amf->guaranteedXP = 1000;
    // $amf->fastestPlaytime = 1000; // seconds
    // $amf->indexed = true;
    // $amf->success=true;
    $mission = missions::where('id', $mid)->get()->first();
    if ($mission->count() > 0)
    {
        $amf->guaranteedTokens = $mission->guaranteedTokens;
        $amf->guaranteedGold = $mission->guaranteedGold;
        $amf->guaranteedModelId = $mission->guaranteedModelId;
        $amf->creatorUserId = $mission->creatorUserId;
        $amf->creatorUserFirstName = $fnc->getUserByID($mission->creatorUserId, 'firstName');
        $amf->creatorUserLastName = $fnc->getUserByID($mission->creatorUserId, 'lastName');
        $amf->creatorAvatarNameInstance = $mission->creatorAvatarNameInstance;
        $amf->creatorAvatarId = $mission->creatorAvatarId;
        $amf->creatorAvatarFName = $fnc->getAvatarByID($mission->creatorAvatarId, 'firstName');
        $amf->creatorAvatarLName = $fnc->getAvatarByID($mission->creatorAvatarId, 'lastName');
        $amf->bonusModelId = $mission->bonusModelId;
        $amf->active = $mission->active;
        $amf->indexed = $mission->indexed;
        $amf->totalPlaytime = $mission->totalPlaytime;
        $amf->timestamp = $mission->timestamp;
        $amf->id = $mission->id;
        $amf->title = $mission->title;
        $amf->desc = $mission->desc;
        $amf->completedDesc = $mission->completedDesc;
        $amf->goodMission = $mission->goodMission;
        $amf->bonusGold = $mission->bonusGold;
        $amf->votes = $mission->votes;
        $amf->bonusTokens = $mission->bonusTokens;
        $amf->xpLevelTypeId = $mission->xpLevelTypeId;
        $amf->reversalTime = $mission->reversalTime;
        $amf->featuredIndex = $mission->featuredIndex;
        $amf->creatorAvatarOnline = null;
        $amf->totalPlaytime = $mission->totalPlaytime; //seconds
        $amf->rating = $mission->rating;
        $amf->firstPlayed = $mission->firstPlayed; // DATE
        $amf->entryTokens = $mission->entryTokens;
        $amf->totalPlays =$mission->totalPlays;
        $amf->lastActivatedSpaceId = $mission->lastActivatedSpaceId;
        $amf->spaceDesc = null;
        $amf->guaranteedXP = $mission->guaranteedXP;
        $amf->fastestPlaytime = $mission->fastestPlaytime; // seconds
        $amf->indexed = $mission->indexed;
        $amf->success = true;
      // }
    }
    else{
      $amf->success = false;
    }
    return $amf;
  }


}

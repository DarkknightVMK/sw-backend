<?php
use App\result\BooleanResult;
use App\result\ServiceResult;
use App\Models\avatarFriends;
class request
{
  public function hasRequest($timeconfig, $aid)
  {
    return new BooleanResult(false);
  }

  public function sendRequest($timeconfig, $aid)
  {
    return new ServiceResult();
  }

  public function acceptRequest($params)
  {
    $aid = $params->avatarId;
    $id = $params->id; // request id in DB 
    $uid = $params->userId; // not used

    $fr = avatarFriends::find(intval($id));
    $fr->reciprocal = true;
    $fr->save();
 
    return new ServiceResult();
  }
}
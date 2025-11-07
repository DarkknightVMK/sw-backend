<?php
 namespace App\result\spintowin;
 use App\result\ServiceResult;
 use Carbon\Carbon;
  use App\Models\s2wCat;
  use App\Models\s2wTiming;

  #[\AllowDynamicProperties]
class InitStateResult extends ServiceResult
{
  public $_explicitType = 'com.smallworlds.widget.spintowin.core.external.result.InitStateResult';

  function __construct($user, $deluxe = null, $winners = null)
  {
    $this->currentTime =  new \Amfphp_Core_Amf_Types_Date( Carbon::createFromTimestamp(strtotime(Carbon::now()))->valueOf());
    // $this->recentWinners = $winners; // TODO
    $this->deluxePrizes = $deluxe; // TODO
    $this->deluxeSpinsRemaining = $user->deluxeSpinsRemaining;
    // var_dump($this->timeRemaining($user->cooldownTimeRemaining));
    $this->cooldownTimeRemaining = $this->timeRemaining($user->cooldownTimeRemaining); // todo get current time and subtract from cooldown time
    parent::__construct(true);
    return $this;
  }

  public static function timeRemaining($cooldown)
  {
    $now = Carbon::now();
    if ($cooldown == null) {
      return 0.0;
    }
    $cooldown = Carbon::createFromTimestamp(strtotime($cooldown));
    $cooldownTime = s2wTiming::where('id', 1)->first()->duration;
    $timeRemaining = $cooldown->diffInSeconds($now);
    if ($timeRemaining > $cooldownTime) {
      return 0.0;
    } else {
      return (float)$cooldownTime - $timeRemaining;
    }
    
    return (float) $timeRemaining;
  }
}
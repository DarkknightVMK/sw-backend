<?php
 namespace App\result\spintowin;
 use App\result\ServiceResult;
 use App\result\spintowin\data\SpinPrize;

 #[\AllowDynamicProperties]

 class RecentWinnersResult extends ServiceResult
 {

  public $_explicitType = 'com.smallworlds.widget.spintowin.core.external.result.DeluxePrizeResult';

  function __construct($winner)
  {
    $this->recentWinners = $winner;
    parent::__construct(true);
    return $this;
  }
 }
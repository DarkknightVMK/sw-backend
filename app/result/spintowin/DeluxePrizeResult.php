<?php
 namespace App\result\spintowin;
 use App\result\ServiceResult;
 use App\result\spintowin\data\SpinPrize;

 #[\AllowDynamicProperties]
 class DeluxePrizeResult extends ServiceResult
 {

  public $_explicitType = 'com.smallworlds.widget.spintowin.core.external.result.DeluxePrizeResult';

  function __construct($prize, $spins)
  {
    $this->prize = new SpinPrize($prize);
    $this->spinsRemaining = $spins;
    parent::__construct(true);
    return $this;
  }
 }
<?php
 namespace App\result\spintowin;
 use App\result\spintowin\data\SpinPrizeCategory;
 use App\result\ServiceResult;
 use App\result\spintowin\data\SpinPrize;
 use App\Models\s2wTiming;

 require_once(__DIR__ . '../../../../resources/php/config.php');

 #[\AllowDynamicProperties]
 class FreePrizeResult extends ServiceResult
 {

  public $_explicitType = 'com.smallworlds.widget.spintowin.core.external.result.FreePrizeResult';

  function __construct($prize = null, $categories = null)
  {
    $this->prize = new SpinPrize($prize);
    $this->categories = $categories;
    $this->cooldown = s2wTiming::where('id', 1)->first()->duration;
    parent::__construct(true);
    return $this;
  }
 }
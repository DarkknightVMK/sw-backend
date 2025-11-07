<?php
namespace App\result;

#[\AllowDynamicProperties]
class IntegerResult extends ServiceResult
{
  public $_explicitType = 'com.smallworlds.service.result.IntegerResult';

  public function __construct($s)
  {
    $this->value = (int) $s;
    parent::__construct(true);
    return $this;
  }
}
<?php

namespace App\result;
#[\AllowDynamicProperties]
class ListResult extends ServiceResult 
{
  public $_explicitType = 'com.smallworlds.service.result.ListResult';
  public function __construct($list)
  {
    $this->list = $list;
    parent::__construct(true);
    return $this;
  }
}
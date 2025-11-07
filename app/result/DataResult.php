<?php
namespace App\result;


class DataResult extends ServiceResult
{
  public $_explicitType = 'com.smallworlds.service.result.DataResult';

  public $data;


  public function __construct($s)
  {
    $this->data = $s;
    parent::__construct(true);
    return $this;
  }
}
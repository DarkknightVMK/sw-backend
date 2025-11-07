<?php
namespace App\result;

#[\AllowDynamicProperties]
class BooleanResult extends ServiceResult
{
  public $value;

  public function __construct($s)
  {
    $this->value = $s;
    parent::__construct(true);
    return $this;
  }
}
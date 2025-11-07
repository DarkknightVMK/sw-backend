<?php
namespace App\result;
use App\Http\Services\functions;
use App\Models\userGroups;
use App\Models\pets;

#[\AllowDynamicProperties]
class StringResult extends ServiceResult
{
  public $_explicitType = 'com.smallworlds.service.result.StringResult';

  public $data;

  public function __construct($s)
  {
    $this->data = $s;
    parent::__construct(true);
    return $this;
  }
}
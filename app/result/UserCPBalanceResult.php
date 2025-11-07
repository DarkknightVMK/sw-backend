<?php
namespace App\result;

#[\AllowDynamicProperties]
class UserCPBalanceResult extends ServiceResult
{
  public $citizenPoints;
  public $citizenLevel;
  
  public function __construct($user)
  {
    $this->citizenPoints = strval($user->citizenPoints);
    $this->citizenLevel =(float) $user->citizenLevel;
    parent::__construct(true);
    return $this;
  }
}
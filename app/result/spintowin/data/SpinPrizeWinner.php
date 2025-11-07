<?php
 namespace App\result\spintowin\data;

 #[\AllowDynamicProperties]
class SpinPrizeWinner
{
  function __construct($avatar, $prize)
  {
    // $this->avatarImage = ; // avatar thumb
    $this->avatarName = $avatar->fullName;
    $this->prize = $prize; //:SpinPrize
    // $this->prizeTime = ; //:date
    return $this;

  }
}
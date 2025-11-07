<?php
 namespace App\result\user;

 use App\result\ServiceResult;

 #[\AllowDynamicProperties]
 class balanceResult extends ServiceResult
 {
  function __construct($user)
    {
      $this->gold =  (float) $user->goldBalance;
      $this->tokens = (float) $user->tokenBalance;
      parent::__construct(true);
      return $this;

    }
 }

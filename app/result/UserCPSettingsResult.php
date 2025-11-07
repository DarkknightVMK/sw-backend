<?php
namespace App\result;

#[\AllowDynamicProperties]
class UserCPSettingsResult extends ServiceResult
{
  public $selectedThemeKey;
  public $overrideRankMinCL;
  
  public function __construct($avatar)
  {
    $this->selectedThemeKey = $avatar->selectedCPThemeKey;
    $this->overrideRankMinCL = $avatar->overrideCPRankMinCL;
    parent::__construct(true);
    return $this;
  }
}
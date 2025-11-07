<?php
namespace App\result\xp;

#[\AllowDynamicProperties]
class AvatarXP
{
  public $_explicitType = "com.smallworlds.entity.avatar.xp.core.dao.data.AvatarXP";

  public function __construct($xp)
  {
    $this->id = strval($xp->id);
    $this->level = (float) $xp->level;
    $this->avatarId = $xp->avatar_id;
    $this->xp = floatval($xp->xp);
    $this->xpType = strval($xp->xpleveltype);
    $this->highestLevel = (float)$xp->level;
    return $this;
  }
}
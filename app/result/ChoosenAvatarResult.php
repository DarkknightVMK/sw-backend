<?php
namespace App\result;
use App\Http\Services\functions;
use App\Models\userGroups;
use App\Models\pets;


class ChoosenAvatarResult extends ServiceResult
{
  public $thumbPostfix;
  public $headPostfix;
  public $snapshotPostfix;
  public $avatarId;
  public $avatarName;
  public $avatarFName;
  public $avatarLName;
  public $avatarNameInstance = 0;
  public $avatarPremiumOptions;
  public $avatarUniqueSpaceVisits = 0;
  public $artifacts = null;
  public $homeSpaceId = null;
  public $timeInSpaces = 0;
  public $config;
  public $missionKeys = null;
  public $explicitTypeField;
  
  public function __construct($id, $pet = false)
  {
    if ($pet == true)
      $pet = pets::find($id);
    
    $primaryGroup = functions::getUser('primaryGroupId');
    $group = userGroups::where('id', $primaryGroup)->pluck('permissionId');
    $this->thumbPostfix = $pet->thumbPostfix;
    $this->headPostfix = $pet->headPostfix;
    $this->snapshotPostfix = $pet->snapshotPostfix;
    $this->avatarId = $pet->stringId;
    $this->avatarName = '';
    $this->avatarFName = $pet->name;
    $this->avatarLName = '';
    $this->config = $pet->configString;

    // $amf = new stdClass();
    // $this->explicitTypeField = Amfphp_Core_Amf_Constants::FIELD_EXPLICIT_TYPE;
    // $this->explicitTypeField = "com.smallworlds.entity.avatar.external.amf.result.ChoosenAvatarResult";
    // $this->success = new ServiceResult(true);
      if (str_contains($group, UserPermissions::GUI_HAS_VIP_PERMISSIONS))
        $this->avatarPremiumOptions = "Y";
      else
        $this->avatarPremiumOptions = "N";
    
    parent::__construct(true);
    return $this;
  }

}
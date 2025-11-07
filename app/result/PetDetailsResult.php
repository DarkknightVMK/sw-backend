<?php
namespace App\result;


class PetDetailsResult extends ServiceResult
{
  public $configString;
  public $active;
  public $name;
  public $motto;
  public $id;
  public $snapshotUrl;
  public $headUrl;
  public $thumbUrl;
  public $ownerId;
  public $memoryString;


  public function __construct($pet)
  {
    $this->configString = $pet->configString;
    $this->active = boolval($pet->active);
    $this->name = $pet->name;
    $this->motto = $pet->motto;
    $this->id = $pet->stringId;
    $this->snapshotUrl = $pet->snapshotUrl;
    $this->headUrl = $pet->headUrl;
    $this->thumbUrl = $pet->thumbUrl;
    $this->ownerId = strval($pet->ownerId);
    $this->memoryString = $pet->memoryString;
    parent::__construct(true);
  //  $this->data[] = $pet->toArray();
    return $this;
  
}



}

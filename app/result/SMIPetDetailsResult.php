<?php
namespace App\result;

#[\AllowDynamicProperties]
class SMIPetDetailsResult 
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
    $this->configString = CompressionUtil::decompress($pet->configString);
    $this->active = boolval($pet->active);
    $this->name = $pet->name;
    $this->motto = $pet->motto;
    $this->id = $pet->stringId;
    $this->snapshotUrl = $pet->snapshotUrl;
    $this->headUrl = $pet->headUrl;
    $this->thumbUrl = $pet->thumbUrl;
    $this->ownerId = strval($pet->ownerId);
    $memoryString = CompressionUtil::decompress($pet->memoryString);
    //split \r\n.join\r 
    $memoryString = str_replace("\r\n", "\r", $memoryString);
    $memoryString = str_replace("\n", "\r", $memoryString);
    $this->memoryString = $memoryString;
    // parent::__construct(true);
  //  $this->data[] = $pet->toArray();
    return $this;
  
}



}

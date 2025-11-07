<?php 
namespace App\result;

#[\AllowDynamicProperties]
class UntypedRecordSetResult extends ServiceResult
{
  public $_explicitType = 'com.smallworlds.service.result.UntypedRecordSetResult';
  
  public function __construct($recordSet)
  {
      $this->recordSet = $recordSet;
      $this->startIndex = 0;
      $this->maxLength = 0;
      $this->totalCount = 0;
      parent::__construct(true);
      return $this;
  }
}
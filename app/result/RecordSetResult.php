<?php

namespace App\result;

#[\AllowDynamicProperties]
class RecordSetResult extends ServiceResult
{
    public $_explicitType = 'com.smallworlds.service.result.RecordSetResult';
    public function __construct($recordSet)
    {
        parent::__construct(true);
        $this->recordSet = $recordSet;
        $this->startIndex = 0;
        $this->maxLength = 0;
        $this->totalCount = 0;
        return $this;
    }
}
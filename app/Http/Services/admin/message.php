<?php
use App\Http\Classes\ErrorCodes;
use App\Http\Classes\ServiceResult;
use App\Http\Classes\StringResult;
use App\Http\Classes\TypedRecordSetResult;
use App\Http\Classes\data\MessageDetails;

class message
{
  function viewUsersInbox($timeconfig,$uid,$limit,$offset, $from, $to)
  {
    //only use from & to if limit is false
    $message = null;
    return new TypedRecordSetResult(
      // return MessageDetails objects
      array_fill(0, $limit, new MessageDetails($message))
      
    );
  }

  function viewUsersSentMessages($timeconfig,$uid,$limit,$offset, $from, $to)
  {
    //only use from & to if limit is false
    return new ServiceResult;
  }

}
<?php
//serviceResult class
namespace App\result;
#[\AllowDynamicProperties]
class ServiceResult 
{

  public $success;
  public $_explicitType = 'com.smallworlds.service.result.ServiceResult';

  public function __construct()
  {
    $a = func_get_args();
    $i = func_num_args();
    if (method_exists($this,$f='__construct'.$i)) {
        call_user_func_array(array($this,$f),$a);
    }
    else
      $this->success = true;
    // return $this;
  }

  public function __construct1($s)
  {
    // $this = new stdClass();
    $this->success = $s;
    // return $this;
  }
  public function __construct2($s, $c)
  {
    // $this = new stdClass();
    $this->success = $s;
    $this->code = (float) $c;
    // return $this;
  }

  public function getSuccess()
  {
    return $this->success;
  }

  public function setSuccess($success)
  {
    $this->success = $success;
  }

  public function getCode()
  {
    return $this->code;
  }

  public function setCode($code)
  {
    $this->code = $code;
  }

  public function show()
  {
    echo $this->success;
  }

  

}
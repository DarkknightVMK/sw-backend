<?php 
use App\Models\Users;
class plant 
{
  function initializePanel()
  {
    $user = Users::find(session('user'));
    $amf = new stdClass();
    $amf->tokensBalance =  $user->tokenBalance;
    $amf->goldBalance = $user->goldBalance;
    $amf->serverDate = new Amfphp_Core_Amf_Types_Date(Carbon\Carbon::now()->valueOf());
    $amf->success = true;
    return $amf;
  }


}
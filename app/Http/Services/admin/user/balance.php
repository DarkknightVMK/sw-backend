<?php
use App\Models\Users;

class balance
{
  function addGoldToUser($timeconfig, $user, $gold, $reason)
  {
    $amf = new stdClass();
    $user = Users::find($user);
    $user->goldBalance = $user->goldBalance + $gold;
    $user->save();
    $amf->success = true;
    // $log = new Logs();
    // $log->user_id = $user->id;
    // $log->admin_id = $admin->id;
    // $log->reason = $reason;
    // $log->gold = $gold;
    // $log->save();
    return $amf;
  }

  function removeGoldFromUser($timeconfig, $user, $gold, $reason)
  {
    $amf = new stdClass();
    $user = Users::find($user);
    $user->goldBalance = $user->goldBalance - $gold;
    $user->save();
    $amf->success = true;
    // $log = new Logs();
    // $log->user_id = $user->id;
    // $log->admin_id = $admin->id;
    // $log->reason = $reason;
    // $log->gold = $gold;
    // $log->save();
    return $amf;
  }


  function addTokensToUser($timeconfig, $user, $tokens, $reason)
  {
    $amf = new stdClass();
    $user = Users::find($user);
    $user->tokenBalance = $user->tokenBalance + $tokens;
    $user->save();
    $amf->success = true;
    // $log = new Logs();
    // $log->user_id = $user->id;
    // $log->admin_id = $admin->id;
    // $log->reason = $reason;
    // $log->tokens = $tokens;
    // $log->save();
    return $amf;
  }

  function removeTokensFromUser($timeconfig, $user, $tokens, $reason)
  {
    $amf = new stdClass();
    $user = Users::find($user);
    $user->tokenBalance = $user->tokenBalance - $tokens;
    $user->save();
    $amf->success = true;
    // $log = new Logs();
    // $log->user_id = $user->id;
    // $log->admin_id = $admin->id;
    // $log->reason = $reason;
    // $log->tokens = $tokens;
    // $log->save();
    return $amf;
  }




}

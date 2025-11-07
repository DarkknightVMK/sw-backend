<?
// namespace App\Http\Services\user;

use App\Models\Users;
use App\result\user\balanceResult;

class balance
{

  function getMyAccountBalances($mypa = null)
  {
    $user = Users::where('id', session('user'))->first();
    return new balanceResult($user);

  }



}
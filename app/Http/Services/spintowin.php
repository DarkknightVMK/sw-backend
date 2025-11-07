<?php

use App\result\spintowin\InitStateResult;
use App\result\spintowin\FreePrizeResult;
use App\result\spintowin\SpinPriceDetails;
use App\result\spintowin\DeluxePrizeResult;
use App\result\spintowin\data\SpinPrizeCategory;
use App\result\spintowin\RecentWinnersResult;
use App\result\ServiceResult;
use App\result\ErrorCodes;
use App\result\IntegerResult;
use App\result\ListResult;
use App\Models\Users;
use App\Models\s2wCat;
use App\Models\s2wPrize;
use App\Models\s2w_SpinPriceDetails;
use App\Models\onlineUsers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

require_once(__DIR__ . '../../../../resources/php/config.php');

class spintowin
{
    function getInitState()
    {
        return new InitStateResult(
            Users::find(session('user')), json_decode(s2wCat::where('isDeluxe', true)->where('active', true)->get()->map(function ($cat) {
                return new SpinPrizeCategory($cat, true);
            })));
    }
    function getSpinPrices()
    {  
        return new ListResult(
            json_decode(s2w_SpinPriceDetails::all()->map(function ($spinPrice) {
            return new SpinPriceDetails($spinPrice);
        })));
    }
    function getFreePrize($true = null)
    {
        $user = Users::find(session('user'));
        // ERROR CHECKING, You can't spin if you have a cooldown
        if ($user->cooldownTimeRemaining != null) 
        {
            $cooldown = InitStateResult::timeRemaining($user->cooldownTimeRemaining);
            if ($cooldown > 0)
                return new ServiceResult(false, ErrorCodes::INTERNAL_ERROR);
        }
        $user->cooldownTimeRemaining = Carbon::now();
        $user->save();
        $cate = s2wCat::where('isDeluxe', false)->where('active', true)->get();
        $totalWeight = 0.0;
        foreach($cate as $cat) 
        {
            $totalWeight += $cat->weighting;
            $currWeight = $cat->weighting / $totalWeight * 100;

            if($currWeight >= 1)
                $currWeight = $this->roundDec($currWeight,0);
            else if($currWeight >= 0.1)
                $currWeight = $this->roundDec($currWeight,1);
            else if($currWeight >= 0.01)
                $currWeight = $this->roundDec($currWeight,2);
            else
                $currWeight = $this->roundDec($currWeight,3);
            
            // store each $currWeight into an array
            $currWeightArray[] = $currWeight;
            $weightValue[] = $cat->weighting;
        }

        foreach ($currWeightArray as $key => $value) 
        {
            $randoCatKey = $this->percent_weighted_random($weightValue);
            $prizes = s2wPrize::where('categoryId', $cate[$randoCatKey]->id)->get();
             // ERROR CHECKING
             if (count($prizes) == 0)
             return new ServiceResult(false, ErrorCodes::SPIN_PRIZE_NOT_FOUND);
             // Grab a random prize from the category
            $prize =  $prizes[rand(0, count($prizes) - 1)];
            // give prize to user
            switch ($prize->type)
            {
                case 1.0:
                    // ITEM
                case 2.0:
                    //GOLD
                    $user->goldBalance += $prize->gold;
                    $user->save();
                    break;
                case 3.0:
                    // tokens
                    $user->tokenBalance += $prize->tokens;
                    $user->save();
                    break;
                default:
                    
            }
            // if ($user->serverIP != '127.0.0.1')
            //     $server = new SabreAMF_Client("https://".SITE_DOMAIN."/local/swds/gateway;jsessionid=".session('id')); // Set up the client object
            // else
            //     $server = new SabreAMF_Client("https://".SITE_DOMAIN."/localhost/swds/gateway;jsessionid=".session('id'));
            // if (onlineUsers::where('online', true)->where('avatar_id', session('avatar'))->count() > 0)
            //     $server->sendRequest('ds.updateBalance', array(session('user'), false, session('id'), session('avatar'))); 

            // // amfphpFlexMessaging 



            // Amfphp_Core_Common_ServiceRouter
            // Amfphp_Core_Common_ServiceRouter::executeServiceCall
        //     
        //     // var_dump($server);
        //     $server->sendRequest('ds.testCall', array(session('id')));

            // $response = Http::withBasicAuth($user->email, $user->password)->post('https://'.SITE_DOMAIN.'/swds/gateway;jsessionid='.session('id'), // amf data
            // [
                
            // ]);
            // $client = new SabreAMF_Client('https://'.SITE_DOMAIN.'/swds/gateway;jsessionid='.session('id').''); // Set up the client object
            // $client->setCredentials($user->email, $user->password); // Set the credentials

            // $client->sendRequest('user.balance.getMyAccountBalances', array('random')); // Send the request
            // $response = $client->getResponseBody(); // Get the response
            // var_dump($response);

            


            return new FreePrizeResult($prize, json_decode(s2wCat::where('isDeluxe', false)->where('active', true)->get()->map(function ($cat) 
            {
                return new SpinPrizeCategory($cat);
            })));
        }
    }

    function purchaseDeluxeSpins($timeconfig, $spinCount, $bool = null)
    {
        $spinDetails = s2w_SpinPriceDetails::where('spins', $spinCount)->first();
        // subtract the price from the user
        $user = Users::find(session('user'));
        $user->goldBalance -= $spinDetails->price;
        // TODO: figure out how to call user service to update the user gold balance in-world or profile

        if ($user->deluxeSpinsRemaining == 0){
            $user->deluxeSpinsRemaining = $spinCount;
            $user->save();
        } 
        else
            return new ServiceResult(false, ErrorCodes::INTERNAL_ERROR);
        return new IntegerResult($spinDetails->spins);
    }

    function getDeluxePrize($bool = null)
    {
        $user = Users::find(session('user'));
        if ($user->deluxeSpinsRemaining > 0) {
            
            $cate = s2wCat::where('isDeluxe', true)->where('active', true)->get();
            $totalWeight = 0.0;
            foreach($cate as $cat) 
            {
                $totalWeight += $cat->weighting;
                $currWeight = $cat->weighting / $totalWeight * 100;

                if($currWeight >= 1)
                    $currWeight = $this->roundDec($currWeight,0);
                else if($currWeight >= 0.1)
                    $currWeight = $this->roundDec($currWeight,1);
                else if($currWeight >= 0.01)
                    $currWeight = $this->roundDec($currWeight,2);
                else
                    $currWeight = $this->roundDec($currWeight,3);
                
                // store each $currWeight into an array
                $currWeightArray[] = $currWeight;
                $weightValue[] = $cat->weighting;
            }

            foreach ($currWeightArray as $key => $value) 
            {
                $randoCatKey = $this->percent_weighted_random($weightValue);
                $prizes = s2wPrize::where('categoryId', $cate[$randoCatKey]->id)->get();
                // ERROR CHECKING
                if (count($prizes) == 0)
                    return new ServiceResult(false, ErrorCodes::SPIN_PRIZE_NOT_FOUND);
                // no error, subtract a spin and proceed
                $user->deluxeSpinsRemaining--;
                $user->save();
                $prize =  $prizes[rand(0, count($prizes) - 1)];
                return new DeluxePrizeResult($prize, $user->deluxeSpinsRemaining);
            }
        } else
            return new ServiceResult(false, "You don't have any deluxe spins");
        return new ServiceResult;
    }

    function processDeluxeWin($bool)
    {
        $user = Users::find(session('user'));
        if ($bool) {
            // return new RecentWinnersResult;
            //TODO
        }
        return new ServiceResult;
    }

    private function roundDec($param1, $param2)
    {
        $loc3_ = pow(10,$param2);
        return round($param1 * $loc3_) / $loc3_;
    }
    // weighted percents, choose one
    private function percent_weighted_random($percentages) 
    {
        $rand = mt_rand(1, (int) array_sum($percentages));
        foreach ($percentages as $key => $percentage) 
        {
            $rand -= $percentage;
            if ($rand <= 0) 
                return $key;
        }
    }
}

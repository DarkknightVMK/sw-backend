<?php 
use App\result\StringResult;
use App\Models\items;
use App\Models\avatarItems;
use App\Models\Avatars;
use App\Models\Users;

class itemtrader
{
// array of params 
    function  initiateTrade($resp)
    {
        $resp->user1Id;
        $resp->user2Id;
        $resp->avatar1Id;
        $resp->avatar2Id;
        $resp->avatar1Gold;
        $resp->avatar2Gold;
        $resp->avatar1Tokens;
        $resp->avatar2Tokens;

            // TODO: check if tradeId is valid
            //transfer items from user1 to user2 and vice versa
            //update trade status to 1

            // $trade = new Trade();
            //increment user1Id goldBalance by avatar2Gold
           

            
        return new StringResult($resp->tradeId);
    }
  }
<?php

namespace App\Helpers;

use App\Models\Hand;
use App\Models\Stack;

class BetHelper
{
    public static function handle(Hand $hand, Stack $stack, $betAmount = null)
    {
        if($betAmount){
            $hand->pot->increment('amount', $betAmount) ;
            $stack->decrement('amount', $betAmount);

            return $betAmount;
        }

        return null;
    }
}

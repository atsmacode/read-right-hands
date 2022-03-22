<?php

namespace App\Helpers;

use App\Models\Pot;
use App\Models\Stack;

class PotHelper
{
    public static function awardPot(Pot $pot, Stack $stack)
    {
        $stack->increment('amount', $pot->amount);
    }
}

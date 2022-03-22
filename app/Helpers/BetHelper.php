<?php

namespace App\Helpers;

use App\Models\Hand;
use App\Models\Player;

class BetHelper
{
    public static function handle(Hand $hand, Player $player, $betAmount = null)
    {
        if($betAmount){

            $hand->pot->increment('amount', $betAmount) ;
            $player->stacks
                ->where('table_id', $hand->table_id)
                ->first()
                ->decrement('amount', $betAmount);

            return $betAmount;

        }

        return null;
    }
}

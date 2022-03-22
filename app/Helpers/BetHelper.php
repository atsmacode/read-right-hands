<?php

namespace App\Helpers;

use App\Models\Hand;
use App\Models\Player;

class BetHelper
{
    public static function handle(Hand $hand, Player $player, $betAmount = null)
    {
        if($betAmount){

            $player->stacks
                ->where('table_id', $hand->handTable->id)
                ->first()
                ->decrement('amount', $betAmount);

            return $betAmount;

        }

        return null;
    }
}

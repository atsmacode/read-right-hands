<?php

namespace App\Helpers;

use App\Models\Player;
use App\Models\Pot;

class PotHelper
{
    public static function awardPot(Pot $pot, Player $player)
    {
        $player->stacks
            ->where('table_id', $pot->handTable->id)
            ->first()
            ->increment('amount', $pot->amount);
    }
}

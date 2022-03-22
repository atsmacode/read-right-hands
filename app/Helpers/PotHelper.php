<?php

namespace App\Helpers;

use App\Models\Hand;
use App\Models\Player;
use App\Models\Pot;
use App\Models\Table;

class PotHelper
{
    public static function initiatePot(Hand $hand)
    {
        $hand->pot()->create(['amount' => 0]);
    }

    public static function awardPot(Pot $pot, Player $player)
    {
        $player->stacks
            ->where('table_id', Table::first()->id)
            ->first()
            ->increment('amount', $pot->amount);
    }
}

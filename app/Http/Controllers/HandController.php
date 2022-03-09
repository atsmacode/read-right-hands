<?php

namespace App\Http\Controllers;

use App\Classes\Dealer;
use App\Classes\Game;
use App\Classes\GamePlay;
use App\Classes\PotLimitHoldEm;
use App\Models\Hand;
use App\Models\Table;
use Illuminate\Http\Request;

class HandController extends Controller
{
    public function new(Request $request)
    {
        // Create a new Hand model

        return (new GamePlay(
            new PotLimitHoldEm(),
            new Dealer(),
            new Hand(),
            new Table(),
            $request->players
        ))->actionOn();
    }

    public function action(Request $request)
    {
        // Create add a new PlayerAction

        return (new GamePlay(
            new PotLimitHoldEm(),
            new Dealer(),
            new Hand(),
            new Table(),
            $request->players
        ))->actionOn();
    }
}

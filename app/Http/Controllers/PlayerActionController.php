<?php

namespace App\Http\Controllers;

use App\Classes\GamePlay;
use App\Models\PlayerAction;
use Illuminate\Http\Request;

class PlayerActionController extends Controller
{
    public function new(Request $request)
    {
        $playerAction = PlayerAction::create([
            $request
        ]);

        return (new GamePlay($playerAction->hand))->play();
    }
}

<?php

namespace App\Http\Controllers;

use App\Classes\GamePlay;
use App\Models\Hand;
use Illuminate\Http\Request;

class HandController extends Controller
{
    public function new(Request $request)
    {
        $hand = Hand::create();

        $gameData = (new GamePlay($hand))->start();

        return response([
            'game_play' => $gameData['gamePlay'],
            'hand' => $gameData['hand'],
            'handTable' => $gameData['handTable'],
            'actions' => $gameData['actions'],
            'streets' => $gameData['streets'],
            'communityCards' => $gameData['communityCards'],
            'wholeCards' => $gameData['wholeCards'],
            'actionOn' => $gameData['actionOn'],
            'winner' => $gameData['winner']
        ]);
    }
}

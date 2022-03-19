<?php

namespace App\Http\Controllers;

use App\Classes\GamePlay;
use App\Models\Hand;
use App\Models\TableSeat;
use Illuminate\Http\Request;

class HandController extends Controller
{
    public function new(Request $request)
    {

        TableSeat::query()
            ->update([
                'can_continue' => 0
            ]);

        if($request->expectsJson()){

            $hand = Hand::create();

            $gameData = (new GamePlay($hand))->start();

            return response()->json([
                'game_play' => json_encode($gameData['gamePlay']),
                'deck' => $gameData['deck'],
                'hand' => $gameData['hand'],
                'handTable' => $gameData['handTable'],
                'actions' => $gameData['actions'],
                'streets' => $gameData['streets'],
                'communityCards' => $gameData['communityCards'],
                'players' => $gameData['players'],
                'winner' => $gameData['winner']
            ]);
        }

        return view('index');
    }
}

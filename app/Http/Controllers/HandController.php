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

            $gamePlay = (new GamePlay($hand))->start();

            return response()->json([
                'deck' => $gamePlay['deck'],
                'communityCards' => $gamePlay['communityCards'],
                'players' => $gamePlay['players'],
                'winner' => $gamePlay['winner']
            ]);
        }

        return view('index');
    }
}

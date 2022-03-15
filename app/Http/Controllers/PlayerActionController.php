<?php

namespace App\Http\Controllers;

use App\Models\PlayerAction;
use Illuminate\Http\Request;

class PlayerActionController extends Controller
{
    public function action(Request $request)
    {
        $playerAction = PlayerAction::where([
            'player_id' =>  $request->player_id,
            'table_seat_id' =>  $request->table_seat_id,
            'hand_street_id' => $request->hand_street_id
        ])->first();

        $playerAction->update([
            'action_id' => $request->action_id,
            'bet_amount' => $request->bet_amount,
            'active' => $request->active
        ]);

        $gameData = $request['game_play']->play();

        return response([
            'game_play' => $gameData['gamePlay'],
            'hand' => $gameData['hand'],
            'handTable' => $gameData['handTable'],
            'actions' => $gameData['actions'],
            'streets' => $gameData['streets'],
            'cards' => $gameData['cards']
        ]);
    }
}

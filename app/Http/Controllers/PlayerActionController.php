<?php

namespace App\Http\Controllers;

use App\Classes\GamePlay;
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
            'active' => 1
        ]);

        return response((new GamePlay($playerAction->hand))->play());
    }
}

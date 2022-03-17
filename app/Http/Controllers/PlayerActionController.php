<?php

namespace App\Http\Controllers;

use App\Models\PlayerAction;
use App\Models\Hand;
use Illuminate\Http\Request;
use App\Classes\GamePlay;

class PlayerActionController extends Controller
{
    public function action(Request $request)
    {
        $playerAction = PlayerAction::where([
            'player_id' =>  $request->player_id,
            'table_seat_id' =>  $request->table_seat_id,
            'hand_street_id' => $request->hand_street_id
        ])->first();

    
        /*
         * Manually set updated_at in this way because framework will not 
         * change the value if the action_id is the same as last street.
        */
        $playerAction->action_id = $request->action_id;
        $playerAction->bet_amount = $request->bet_amount;
        $playerAction->active = $request->active;
        $playerAction->updated_at = now(); 
        $playerAction->save();

        $gameData = (new GamePlay(Hand::query()->latest()->first(), $request->deck))->play();

        return response()->json([
            'deck' => $gameData['deck'],
            'game_play' => $gameData['gamePlay'],
            'hand' => $gameData['hand'],
            'handTable' => $gameData['handTable'],
            'actions' => $gameData['actions'],
            'streets' => $gameData['streets'],
            'communityCards' => $gameData['communityCards'],
            'wholeCards' => $gameData['wholeCards'],
            'players' => $gameData['players'],
            'winner' => $gameData['winner']
        ]);
    }
}

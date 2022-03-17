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
         * A hacky way to resolve updated_at not changing if the action_id i the same.
         * Issue happened when multiple rounds of re-raising takes place.
         */
        $playerAction->update([
            'action_id' => null,
        ]);

        $playerAction->update([
            'action_id' => $request->action_id,
            'bet_amount' => $request->bet_amount,
            'active' => $request->active
        ]);

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

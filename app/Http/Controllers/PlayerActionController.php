<?php

namespace App\Http\Controllers;

use App\Helpers\BetHelper;
use App\Models\PlayerAction;
use App\Models\Hand;
use Illuminate\Http\Request;
use App\Classes\GamePlay;

class PlayerActionController extends Controller
{
    public function action(Request $request)
    {

        $hand = Hand::query()->latest()->first();

        logger($hand->id);

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
            'bet_amount' => BetHelper::handle($hand, $playerAction->player, $request->bet_amount),
            'active' => $request->active
        ]);

        $gamePlay = (new GamePlay($hand, $request->deck))->play();

        return response()->json([
            'deck' => $gamePlay['deck'],
            'pot' => $gamePlay['pot'],
            'communityCards' => $gamePlay['communityCards'],
            'players' => $gamePlay['players'],
            'winner' => $gamePlay['winner']
        ]);
    }
}

<?php

namespace App\Classes;

use App\Models\Hand;
use App\Models\TableSeat;

class Showdown
{
    public $handIdentifier;
    public $hand;
    public $communityCards = [];
    public $playerHands = [];
    public $winner;

    /**
     * @param Hand $hand
     */
    public function __construct($hand)
    {
        $this->handIdentifier = new HandIdentifier();
        $this->hand = $hand;
    }

    public function decideWinner()
    {
        // Return the player hand with the highest rank
        return collect($this->playerHands)
            ->sortBy(function ($item) {
                return $item['handType']['ranking'];
            })
            ->values()
            ->first();
    }

    public function compileHands()
    {
        $this->getCommunityCards();

        foreach(TableSeat::where('can_continue', 1)->get() as $tableSeat){
            $wholeCards = [];
            foreach($tableSeat->player->wholeCards->where('hand_id', $this->hand->id) as $wholeCard){
                $wholeCards[] = $wholeCard->card;
            }

            $this->playerHands[] = [
                'player' => $tableSeat->player,
                'handType' => $this->handIdentifier->identify($wholeCards, $this->communityCards)->identifiedHandType['handType']
            ];
        }

        return $this;

    }

    public function getCommunityCards()
    {
        foreach($this->hand->streets as $handStreets){
            foreach($handStreets->cards as $handStreetCard){
                $this->communityCards[] = $handStreetCard->card;
            }
        }
    }

}

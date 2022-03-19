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
    protected $considerKickers = false;

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

        /*
         * foreach handType
         * If there are more than 1 players with that hand type
         * Retain only the one with the highest kicker or active cards as appropriate
         * Then compare the hand rankings of each remaining player hand
         */

        $playerHands = collect($this->playerHands);
        $playerHandsReset = null;

        foreach($this->handIdentifier->handTypes as $handType){

            $handsOfHandType = $playerHands->where('handType' , $handType);

            if($handsOfHandType->count() > 1){

                $this->considerKickers = true;

                $playerHandsReset = $playerHands->reject(function($value, $key) use($handType){
                    return $value['handType'] === $handType;
                });

                $highestRankedHandOfThisType = $handsOfHandType->reject(function($value, $key) use($playerHands, $handType){

                    // Only reject less than, if multiple remain with same kicker it's a split pot
                    return $value['kicker'] < $playerHands
                            ->where('handType' , $handType)
                            ->sortByDesc('kicker')
                            ->first()['kicker'];

                })->first();

                $playerHandsReset->push($highestRankedHandOfThisType);

            }

        }

        if($this->considerKickers){
            return $playerHandsReset
                ->sortBy(function ($item) {
                    return $item['handType']->ranking;
                })
                ->values()
                ->first();
        }

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

            $compileInfo = (new HandIdentifier())->identify($wholeCards, $this->communityCards)->identifiedHandType;
            $compileInfo['player'] = $tableSeat->player;

            $this->playerHands[] = $compileInfo;
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

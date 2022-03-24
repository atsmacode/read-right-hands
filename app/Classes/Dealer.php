<?php

namespace App\Classes;

use App\Models\Card;
use App\Models\Hand;
use App\Models\HandStreet;
use App\Models\HandStreetCard;
use App\Models\Player;
use App\Models\Rank;
use App\Models\Suit;
use Illuminate\Database\Eloquent\Collection;

class Dealer
{

    public $deck;
    public $card;

    public function setDeck($deck = null)
    {
        if($deck){
            $this->deck = collect($deck);
        } else {
            $this->deck = Card::all();
        }

        return $this;
    }

    public function getDeck()
    {
        return $this->deck;
    }

    public function shuffle()
    {
        $this->deck = $this->deck->shuffle();

        return $this;
    }

    public function pickCard(Rank $rank = null, Suit $suit = null)
    {
        if($rank === null && $suit === null){
            $this->card = $this->getDeck()->shift();
            return $this;
        }

        $this->card = $this->deck->filter(function($value) use ($rank, $suit){
            return $value->rank_id === $rank->id && $value->suit_id === $suit->id;
        })->first();

        $card = $this->card;

        $this->deck->reject(function($value) use($card){
            return $value === $card;
        });

        return $this;
    }

    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param Collection|Player $players
     * @param Hand $hand
     * @return $this
     */
    public function dealTo($players, $cardCount, $hand = null)
    {
        if($players instanceof Player){

            $dealtCards = 0;
            while($dealtCards < $cardCount){
                $players->wholeCards()->create([
                    'card_id' => $this->pickCard()->getCard()->id,
                    'hand_id' => $hand ? $hand->id : null
                ]);
                $dealtCards++;
            }

            return $this;
        }

        foreach($players as $player){

            $dealtCards = 0;
            while($dealtCards < $cardCount){
                $player->wholeCards()->create([
                    'card_id' => $this->pickCard()->getCard()->id,
                    'hand_id' => $hand ? $hand->id : null
                ]);
                $dealtCards++;
            }

        }

        return $this;

    }

    /**
     * @param Player $player
     * @return $this
     */
    public function dealCardTo($player)
    {

        $player->wholeCards()->create([
            'card_id' => $this->getCard()->id
        ]);

        return $this;

    }

    /**
     * @param HandStreet $handStreet
     * @return $this
     */
    public function dealStreetCards($handStreet, $cardCount)
    {

        $dealtCards = 0;

        while($dealtCards < $cardCount){

            $cardId = is_object($this->pickCard()->getCard()) ? $this->pickCard()->getCard()->id : $this->pickCard()->getCard()['id'];

            HandStreetCard::create([
                'card_id' => $cardId,
                'hand_street_id' => $handStreet->id
            ]);

            $dealtCards++;

        }

        return $this;

    }

    /**
     * @param HandStreet $handStreet
     * @param Rank $rank
     * @param Suit $suit
     * @return $this
     */
    public function dealThisStreetCard($rank, $suit, $handStreet)
    {

        $cardId = $this->pickCard($rank, $suit)->getCard()->id;

        HandStreetCard::create([
            'card_id' => $cardId,
            'hand_street_id' => $handStreet->id
        ]);

        return $this;

    }
}

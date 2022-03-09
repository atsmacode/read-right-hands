<?php

namespace App\Classes;

use App\Models\Card;
use App\Models\Player;
use App\Models\Rank;
use App\Models\Suit;

class Dealer
{

    public $deck;
    public $card;

    public function setDeck()
    {
        $this->deck = Card::all();

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
            $this->card = $this->deck->shift();
            return $this;
        }

        $filter = $this->deck->filter(function($value) use ($rank, $suit){
            return $value->rank_id === $rank->id && $value->suit_id === $suit->id;
        });

        $this->card = $filter->first();

        return $this;
    }

    public function dealTo(\Illuminate\Database\Eloquent\Collection $players)
    {
        if($players->count() === 1){
            $players->wholeCard = $players->wholeCards()->create([
                'card_id' => $this->pickCard()->card->id
            ]);
        }

        if($players->count() > 1){
            foreach($players as $player){
                $player->wholeCard = $player->wholeCards()->create([
                    'card_id' => $this->pickCard()->card->id
                ]);
            }
        }
    }
}

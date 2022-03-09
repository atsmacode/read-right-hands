<?php

namespace App\Classes;

use App\Models\Card;
use App\Models\Player;
use App\Models\Rank;
use App\Models\Suit;
use Illuminate\Database\Eloquent\Collection;

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
     * @return $this
     */
    public function dealTo($players)
    {
        if($players->count() === 1){
            $players->wholeCards()->create([
                'card_id' => $this->pickCard()->getCard()->id
            ]);

            return $this;
        }

        if($players->count() > 1){
            foreach($players as $player){
                $player->wholeCards()->create([
                    'card_id' => $this->pickCard()->getCard()->id
                ]);
            }

            return $this;
        }
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
}

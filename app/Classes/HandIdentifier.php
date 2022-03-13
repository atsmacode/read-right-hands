<?php

namespace App\Classes;

use App\Models\HandType;
use App\Models\Rank;
use App\Models\Suit;

class HandIdentifier
{
    protected $handTytpes;
    protected $allCards;
    protected $pairs = [];
    protected $threeOfAKind = false;
    protected $straight = false;
    protected $flush = false;
    protected $fourOfAKind;
    protected $royalFlush = false;

    public function __construct()
    {
        $this->handTytpes = HandType::all();
    }

    public function identify($wholeCards, $communityCards)
    {
        $this->allCards = collect(array_merge($wholeCards, $communityCards))->sortByDesc('ranking')->values();

        return $this;
    }

    public function highestCard()
    {

        if($this->allCards->pluck('ranking')->min() === 1){
            return 1;
        }
        return $this->allCards->pluck('ranking')->max();
    }

    public function hasPair()
    {

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 2){
                $this->pairs[] = $rank;
            }
        }

        return count($this->pairs) === 1;
    }

    public function hasTwoPair()
    {

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 2){
                $this->pairs[] = $rank;
            }
        }

        return count($this->pairs) >= 2;
    }

    public function hasThreeOfAKind()
    {

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 3){
                $this->threeOfAKind = $rank;
            }
        }

        // There could be 2 trips - add handling for this
        return $this->threeOfAKind;
    }

    public function hasStraight()
    {

        $straight = $this->allCards->filter(function($value, $key) {

            $nextCardRankingPlusOne = null;
            $previousCardRankingMinusOne = null;

            if(array_key_exists($key + 1, $this->allCards->toArray())){
                $nextCardRankingPlusOne = $this->allCards[$key + 1]->ranking + 1;
            }

            if(array_key_exists($key - 1, $this->allCards->toArray())){
                $previousCardRankingMinusOne = $this->allCards[$key - 1]->ranking - 1;
            }

            return $value->ranking === $previousCardRankingMinusOne || $value->ranking === $nextCardRankingPlusOne;

        })->unique(function ($item) {

            return $item['rank']['ranking'];

        })->take(5);

        if($straight && count($straight) === 5){
            $this->straight = $straight;
            return true;
        }

        return false;
    }

    public function hasFlush()
    {
        foreach(Suit::all() as $suit){
            if($this->allCards->where('suit_id', $suit->id)->count() >= 5){
                $this->flush = $suit;
            }
        }

        return $this->flush;
    }

    public function hasFullHouse()
    {
        $this->hasTwoPair();
        $this->hasThreeOfAKind();

        return $this->hasTwoPair() && $this->hasThreeOfAKind();
    }

    public function hasFourOfAKind()
    {
        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 4){
                $this->fourOfAKind = $rank;
            }
        }

        return $this->fourOfAKind;
    }

    public function hasStraightFlush()
    {

    }

    public function hasRoyalFlush()
    {

        foreach(Suit::all() as $suit){
            $royalFlush = $this->allCards->filter(function($value, $key) use ($suit){
                return $value->suit_id === $suit->id && $value->rank->name === 'Ace' ||
                    $value->suit_id === $suit->id && $value->rank->name === 'King' ||
                    $value->suit_id === $suit->id && $value->rank->name === 'Queen'||
                    $value->suit_id === $suit->id && $value->rank->name === 'Jack'||
                    $value->suit_id === $suit->id && $value->rank->name === 'Ten';
            });

            if($royalFlush && count($royalFlush) === 5){
                $this->royalFlush = $royalFlush;
                return true;
            }
        }

        return false;
    }
}

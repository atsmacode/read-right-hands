<?php

namespace App\Classes;

use App\Models\HandType;
use App\Models\Rank;
use App\Models\Suit;

class HandIdentifier
{
    protected $handTypes;
    public $identifiedHandType;
    public $allCards;
    public $highCard;
    public $pairs = [];
    public $threeOfAKind = false;
    public $straight = false;
    public $flush = false;
    public $fourOfAKind;
    public $straightFlush = false;
    public $royalFlush = false;

    public function __construct()
    {
        $this->handTypes = HandType::all();
    }

    public function identify($wholeCards, $communityCards)
    {
        $this->allCards = collect(array_merge($wholeCards, $communityCards))->sortByDesc('ranking')->values();

        return $this;
    }

    public function highestCard()
    {

        if($this->allCards->pluck('ranking')->min() === 1){
            $this->highCard = 1;
        } else {
            $this->highCard = $this->allCards->pluck('ranking')->max();
        }

        $this->identifiedHandType = $this->handTypes->where('name', 'High Card')->first();

        return $this;
    }

    public function hasPair()
    {

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 2){
                $this->pairs[] = $rank;
            }
        }

        if(count($this->pairs) === 1){
            $this->identifiedHandType = $this->handTypes->where('name', 'Pair')->first();
            return true;
        }

        return $this;
    }

    public function hasTwoPair()
    {

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 2){
                $this->pairs[] = $rank;
            }
        }

        if(count($this->pairs) >= 2){
            $this->identifiedHandType = $this->handTypes->where('name', 'Two Pair')->first();
            return true;
        }

        return $this;
    }

    public function hasThreeOfAKind()
    {

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 3){
                $this->threeOfAKind = $rank;
                $this->identifiedHandType = $this->handTypes->where('name', 'Three of a Kind')->first();
                return true;
            }
        }

        // There could be 2 trips - add handling for this
        return $this;
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
            $this->identifiedHandType = $this->handTypes->where('name', 'Straight')->first();
            return true;
        }

        return $this;
    }

    public function hasFlush()
    {
        foreach(Suit::all() as $suit){
            if($this->allCards->where('suit_id', $suit->id)->count() >= 5){
                $this->flush = $suit;
                $this->identifiedHandType = $this->handTypes->where('name', 'Flush')->first();
                return true;
            }
        }

        return $this;
    }

    public function hasFullHouse()
    {
        $this->hasTwoPair();
        $this->hasThreeOfAKind();

        if($this->hasTwoPair() && $this->hasThreeOfAKind()){
            $this->identifiedHandType = $this->handTypes->where('name', 'Full House')->first();
            return true;
        }

        return $this;
    }

    public function hasFourOfAKind()
    {
        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 4){
                $this->fourOfAKind = $rank;
                $this->identifiedHandType = $this->handTypes->where('name', 'Four of a Kind')->first();
                return true;
            }
        }

        return $this;
    }

    public function hasStraightFlush()
    {
        foreach(Suit::all() as $suit){
            $straightFlush = $this->allCards->filter(function($value, $key) use ($suit){


                $nextCardRankingPlusOne = null;
                $previousCardRankingMinusOne = null;

                if(array_key_exists($key + 1, $this->allCards->toArray())){
                    $nextCardRankingPlusOne = $this->allCards[$key + 1]->ranking + 1;
                }

                if(array_key_exists($key - 1, $this->allCards->toArray())){
                    $previousCardRankingMinusOne = $this->allCards[$key - 1]->ranking - 1;
                }

                return $value->suit_id === $suit->id &&
                    ($value->ranking === $previousCardRankingMinusOne || $value->ranking === $nextCardRankingPlusOne);
            });

            if($straightFlush && count($straightFlush) === 5){
                $this->straightFlush = $straightFlush;
                $this->identifiedHandType = $this->handTypes->where('name', 'Straight Flush')->first();
                return true;
            }
        }

        return $this;
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
                $this->identifiedHandType = $this->handTypes->where('name', 'Royal Flush')->first();
                return true;
            }
        }

        return $this;
    }
}

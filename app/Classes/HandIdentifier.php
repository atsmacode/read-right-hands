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
    protected $flush = false;
    protected $fourOfAKind;
    protected $royalFlush = false;

    public function __construct()
    {
        $this->handTytpes = HandType::all();
    }

    public function identify($wholeCards, $communityCards)
    {
        $this->allCards = collect(array_merge($wholeCards, $communityCards));

        return $this;
    }

    public function highestCard()
    {

        $max = $this->allCards->filter(function($value, $key){
            if($key > 0){
                return $value->rank->ranking > $this->allCards[$key - 1]->rank->ranking || $value->rank->ranking === 1;
            }

            return $value->rank->ranking > $this->allCards[$key + 1]->rank->ranking || $value->rank->ranking === 1;
        })->first();

        return $max->rank->ranking;
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

        return $this->threeOfAKind;
    }

    public function hasStraight()
    {

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

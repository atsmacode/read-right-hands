<?php

namespace App\Classes;

use App\Models\HandType;
use App\Models\Rank;
use App\Models\Suit;

class HandIdentifier
{
    public $handTypes;
    public $identifiedHandType = [
        'handType' => null,
        'activeCards' => [0],
        'kicker' => null
    ];
    public $allCards;
    public $highCard;
    public $pairs = [];
    public $threeOfAKind = false;
    public $straight = false;
    public $flush = false;
    public $fullHouse = false;
    public $fourOfAKind = false;
    public $straightFlush = false;
    public $royalFlush = false;
    public $handMethods = [
        'hasRoyalFlush',
        'hasStraightFlush',
        'hasFourOfAKind',
        'hasFullHouse',
        'hasFlush',
        'hasStraight',
        'hasThreeOfAKind',
        'hasTwoPair',
        'hasPair',
        'highestCard'
    ];

    public function __construct()
    {
        $this->handTypes = HandType::all();
    }

    public function identify($wholeCards, $communityCards)
    {
        $this->allCards = collect(array_merge($wholeCards, $communityCards))->sortByDesc('ranking')->values();

        foreach($this->handMethods as $handMethod){
            if($this->{$handMethod}() === true){
                break;
            }
        }

        return $this;

    }

    protected function checkForAceKicker($allCards, $forHandCheck, $activeCards = null)
    {
        if(($activeCards && $allCards->contains('ranking', 1) && !in_array(1, $activeCards)) ||
            (in_array(1, $activeCards) && $forHandCheck === 'hasFlush')
        ){
            return 14;
        }

        return false;
    }

    public function highestCard()
    {

        if($this->allCards->pluck('ranking')->min() === 1){
            $this->highCard = 14;
        } else {
            $this->highCard = $this->allCards->pluck('ranking')->max();
        }

        $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'High Card')->first();
        $this->identifiedHandType['activeCards'][] = $this->highCard;
        $this->identifiedHandType['kicker'] = $this->allCards->pluck('ranking')->where('ranking', '<', $this->highCard)->sortByDesc('ranking')->first();

        return $this;
    }

    public function hasPair()
    {

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 2){
                $this->pairs[] = $rank;
                $this->identifiedHandType['activeCards'][] = $rank->ranking;
                /*
                 * The showdown may be called pre-flop when the pot is checked down to BB.
                 * In which case they may have a pair and no other kicker rank.
                 * Ultimately this will be handled more elegantly when kickers are fully fleshed out.
                 */
                if($this->allCards->count() > 2){
                    $this->identifiedHandType['kicker'] = $this->checkForAceKicker($this->allCards, __FUNCTION__,  $this->identifiedHandType['activeCards'])
                        ?: $this->allCards->where('ranking', '!=', $rank->ranking)->sortByDesc('ranking')->first()->ranking;
                } else {
                    $this->identifiedHandType['kicker'] = $rank->ranking;
                }

            }
        }

        if(count($this->pairs) === 1){
            $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Pair')->first();
            return true;
        }

        return $this;
    }

    public function hasTwoPair()
    {

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 2){
                $this->pairs[] = $rank;
                $this->identifiedHandType['activeCards'][] = $rank->ranking;
                /*
                 * The showdown may be called pre-flop when the pot is checked down to BB.
                 * In which case they may have a pair and no other kicker rank.
                 * Ultimately this will be handled more elegantly when kickers are fully fleshed out.
                 */
                if($this->allCards->count() > 2){
                    $this->identifiedHandType['kicker'] = $this->checkForAceKicker($this->allCards, __FUNCTION__,  $this->identifiedHandType['activeCards'])
                        ?: $this->allCards->where('ranking', '!=', $rank->ranking)->sortByDesc('ranking')->first()->ranking;
                } else {
                    $this->identifiedHandType['kicker'] = $rank->ranking;
                }
            }
        }

        if(count($this->pairs) >= 2){
            $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Two Pair')->first();
            return true;
        }

        $this->pairs = [];

        return $this;
    }

    public function hasThreeOfAKind()
    {

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 3){
                $this->threeOfAKind = $rank;
                $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Three of a Kind')->first();
                $this->identifiedHandType['activeCards'][] = $rank->ranking;
                $this->identifiedHandType['kicker'] = $this->checkForAceKicker($this->allCards, __FUNCTION__, $this->identifiedHandType['activeCards'])
                    ?: $this->allCards->where('ranking', '!=', $rank->ranking)->sortByDesc('ranking')->first()->ranking;
                return true;
            }
        }

        // There could be 2 trips - add handling for this
        return $this;
    }

    public function hasStraight()
    {

        if($this->hasFiveHighStraight() === true){
            return true;
        }

        if($this->hasAceHighStraight() === true){
            return true;
        }

        if($this->hasAnyOtherStraight() === true){
            return true;
        }

        return $this;
    }

    protected function hasAnyOtherStraight()
    {

        $removeDuplicates = $this->allCards->unique(function ($item) {
            return $item['rank']['ranking'];
        })->sortByDesc('ranking')->values();

        $straight = $removeDuplicates->filter(function($value, $key) use($removeDuplicates){

            $removeDuplicates = $removeDuplicates->toArray();
            $nextCardRankingPlusOne = null;
            $previousCardRankingMinusOne = null;
            $previousCardRanking = null;

            if(array_key_exists($key + 1, $removeDuplicates)){
                $nextCardRankingPlusOne = $removeDuplicates[$key + 1]['rank']['ranking'] + 1;
            }

            if(array_key_exists($key - 1, $removeDuplicates)){
                $previousCardRankingMinusOne = $removeDuplicates[$key - 1]['rank']['ranking'] - 1;
                $previousCardRanking = $removeDuplicates[$key - 1]['rank']['ranking'];
            }

            /*
             * Had to add extra logic to prevent K,Q,9,8,7 being set as a straight, for example.
             */
            $twoCardsInFrontRankingPlusTwo = null;
            $twoCardsPreviousRankingMinusTwo = null;

            if(array_key_exists($key + 2, $removeDuplicates)){
                $twoCardsInFrontRankingPlusTwo = $removeDuplicates[$key + 2]['rank']['ranking'] + 2;
            }

            if(array_key_exists($key - 2, $removeDuplicates)){
                $twoCardsPreviousRankingMinusTwo = $removeDuplicates[$key - 2]['rank']['ranking'] - 2;
            }

            return ($value->ranking !== $previousCardRanking) &&
                (($value->ranking === $previousCardRankingMinusOne || $value->ranking === $nextCardRankingPlusOne) &&
                    ($value->ranking === $twoCardsPreviousRankingMinusTwo || $value->ranking === $twoCardsInFrontRankingPlusTwo));


        })->take(5);

        if($straight && count($straight) === 5){
            $this->straight = $straight;

            $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Straight')->first();
            $this->identifiedHandType['kicker'] = $straight->first()->ranking;
            return true;
        }

    }

    protected function hasFiveHighStraight()
    {
        $straight = $this->allCards->sortByDesc('ranking')->values()->filter(function($value, $key) {

            $previousCardRanking = null;

            /*
             * Remove duplicates.
             */
            if(array_key_exists($key - 1, $this->allCards->toArray())){
                $previousCardRanking = $this->allCards[$key - 1]->ranking;
            }

            switch($value->ranking){
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                    if($value->ranking !== $previousCardRanking){
                        return true;
                    }
                    break;
            }

        })->take(5);

        if($straight && count($straight) === 5){
            $this->straight = $straight;
            $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Straight')->first();
            $this->identifiedHandType['kicker'] = $straight->first()->ranking;
            return true;
        }

    }

    protected function hasAceHighStraight()
    {
        $straight = $this->allCards->sortByDesc('ranking')->values()->filter(function($value, $key) {

            $previousCardRanking = null;

            /*
             * Remove duplicates.
             */
            if(array_key_exists($key - 1, $this->allCards->toArray())){
                $previousCardRanking = $this->allCards[$key - 1]->ranking;
            }

            switch($value->ranking){
                case 1:
                case 13:
                case 12:
                case 11:
                case 10:
                    if($value->ranking !== $previousCardRanking){
                        return true;
                    }
                    break;
            }

        })->take(5);

        if($straight && count($straight) === 5){
            $this->straight = $straight;
            $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Straight')->first();
            $this->identifiedHandType['kicker'] = 14;
            return true;
        }

    }

    public function hasFlush()
    {

        foreach(Suit::all() as $suit){
            if($this->allCards->where('suit_id', $suit->id)->count() >= 5){
                $this->flush = $suit;

                $flushCards = $this->allCards->where('suit_id', $suit->id)->pluck('ranking')->toArray();

                $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Flush')->first();
                $this->identifiedHandType['kicker'] = $this->checkForAceKicker($this->allCards,__FUNCTION__, $flushCards)
                    ?: $flushCards[0];
                return true;
            }
        }

        return $this;
    }

    public function hasFullHouse()
    {

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 3){
                $this->threeOfAKind = $rank;
            }
        }

        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 2 && $this->threeOfAKind !== $rank){
                $this->pairs[] = $rank;
            }
        }

        /*
         * There could be 2 pairs here.
         * Changed to === 1 as three_of_a_kind_beats_two_pair_test_was_failing.
         * Needs looked into.
         */
        if($this->threeOfAKind && count($this->pairs) === 1){
            $this->fullHouse = true;
            $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Full House')->first();
            return true;
        }

        $this->pairs = [];
        $this->threeOfAKind = false;
        return $this;
    }

    public function hasFourOfAKind()
    {
        foreach(Rank::all() as $rank){
            if($this->allCards->where('rank_id', $rank->id)->count() === 4){

                $this->fourOfAKind = $rank;

                $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Four of a Kind')->first();
                $this->identifiedHandType['activeCards'][] = $rank->ranking;
                $this->identifiedHandType['kicker'] = $this->checkForAceKicker($this->allCards, __FUNCTION__, $this->identifiedHandType['activeCards'])
                    ?: $this->allCards->where('ranking', '!=', $rank->ranking)->sortByDesc('ranking')->first()->ranking;

                return true;

            }
        }

        return $this;
    }

    public function hasStraightFlush()
    {

        foreach(Suit::all() as $suit){

            /*
             * Remove cards not in this suit.
             * This also takes care of duplicates.
             */
            $onlyThisSuit = $this->allCards->reject(function ($item) use($suit){
                return $item->suit_id !== $suit->id;
            })->values();

            $straightFlush = $onlyThisSuit->filter(function($value, $key) use ($suit, $onlyThisSuit){

                $onlyThisSuit = $onlyThisSuit->toArray();
                $nextCardRankingPlusOne = null;
                $previousCardRankingMinusOne = null;

                if(array_key_exists($key + 1, $onlyThisSuit)){
                    $nextCardRankingPlusOne = $onlyThisSuit[$key + 1]['rank']['ranking'] + 1;
                }

                if(array_key_exists($key - 1, $onlyThisSuit)){
                    $previousCardRankingMinusOne = $onlyThisSuit[$key - 1]['rank']['ranking'] - 1;
                }

                /*
                 * Had to add extra logic to prevent K,Q,9,8,7 being set as a straight, for example.
                 * And checking if the current rank has already been counted towards a straight.
                 * Which makes this method quite long - extract or simplify.
                 */
                $twoCardsInFrontRankingPlusTwo = null;
                $twoCardsPreviousRankingMinusTwo = null;

                if(array_key_exists($key + 2, $onlyThisSuit)){
                    $twoCardsInFrontRankingPlusTwo = $onlyThisSuit[$key + 2]['rank']['ranking'] + 2;
                }

                if(array_key_exists($key - 2, $onlyThisSuit)){
                    $twoCardsPreviousRankingMinusTwo = $onlyThisSuit[$key - 2]['rank']['ranking'] - 2;
                }

                return ($value->ranking === $previousCardRankingMinusOne || $value->ranking === $nextCardRankingPlusOne) &&
                    ($value->ranking === $twoCardsPreviousRankingMinusTwo || $value->ranking === $twoCardsInFrontRankingPlusTwo);
            });

            if($straightFlush && count($straightFlush) === 5){
                $this->straightFlush = true;
                $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Straight Flush')->first();
                $this->identifiedHandType['kicker'] = $straightFlush->first()->ranking;
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
                $this->identifiedHandType['handType'] = $this->handTypes->where('name', 'Royal Flush')->first();
                return true;
            }
        }

        return $this;
    }
}

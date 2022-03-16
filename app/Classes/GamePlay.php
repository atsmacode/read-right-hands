<?php

namespace App\Classes;

use App\Models\Action;
use App\Models\HandStreet;
use App\Models\PlayerAction;
use App\Models\Street;
use App\Models\Table;
use App\Models\Hand;
use App\Models\TableSeat;

class GamePlay
{
    public $game;
    public $dealer;
    protected $actionOn;

    public function __construct(Hand $hand)
    {
        $this->game = new PotLimitHoldEm();
        $this->dealer = new Dealer();
        $this->hand = $hand;
        $this->handTable = Table::first();
        $this->street = null;
    }

    public function play()
    {

        $this->updateSeatStatusOfLatestAction();
        $this->updateAllOtherSeatsBasedOnLatestAction();

        return $this->nextStep();

    }

    public function showdown()
    {

        $this->hand->completed_on = now();
        $this->hand->save();

        return [
            'gamePlay' => $this,
            'hand' => $this->hand->fresh(),
            'handTable' => $this->handTable->fresh(),
            'actions' => $this->hand->playerActions->fresh(),
            'streets' => $this->hand->fresh()->streets,
            'communityCards' => $this->getCommunityCards(),
            'wholeCards' => $this->getWholeCards(),
            'players' => $this->getPlayerData(),
            'winner' => (new Showdown($this->hand->fresh()))->compileHands()->decideWinner()
        ];
    }

    public function continue()
    {

        // Not keen on the way I'm adding/subtracting from the handStreets->count() to match array starting with 0
        $this->street = HandStreet::create([
            'street_id' => Street::where('name', $this->game->streets[$this->hand->fresh()->streets->count()]['name'])->first()->id,
            'hand_id' => $this->hand->id
        ]);

        $dealtCards = 0;
        while($dealtCards < $this->game->streets[$this->hand->fresh()->streets->count() - 1]['community_cards']){
            $this->dealer->dealStreetCard($this->street);
            $dealtCards++;
        }

        return [
            'gamePlay' => $this,
            'hand' => $this->hand->fresh(),
            'handTable' => $this->handTable->fresh(),
            'actions' => $this->hand->playerActions->fresh(),
            'streets' => $this->hand->fresh()->streets,
            'communityCards' => $this->getCommunityCards(),
            'wholeCards' => $this->getWholeCards(),
            'actionOn' => $this->getActionOn(),
            'players' => $this->getPlayerData(),
            'winner' => null
        ];
    }

    public function start()
    {

        $this->initiateStreetActions();

        $this->postBlinds();

        $this->dealer->setDeck()->shuffle();

        if($this->game->streets[0]['whole_cards']){
            $dealtCards = 0;
            while($dealtCards < $this->game->streets[0]['whole_cards']){
                $this->dealer->dealTo($this->handTable->fresh()->players, $this->hand);
                $dealtCards++;
            }
        }

        return [
            'gamePlay' => $this,
            'hand' => $this->hand->fresh(),
            'handTable' => $this->handTable->fresh(),
            'actions' => $this->hand->playerActions->fresh(),
            'streets' => $this->hand->streets->fresh(),
            'communityCards' => $this->getCommunityCards(),
            'wholeCards' => $this->getWholeCards(),
            'actionOn' => $this->getActionOn(),
            'players' => $this->getPlayerData(),
            'winner' => null
        ];

    }

    public function nextStep()
    {

        // Showdown
        if($this->hand->fresh()->streets->count() === count($this->game->streets) &&
            $this->hand->fresh()->playerActions->where('active', 1)->count() === $this->handTable->fresh()->tableSeats->where('can_continue', 1)->count()){
            return $this->showdown();
        }

        // If all active players can_continue
        if($this->hand->fresh()->playerActions->where('active', 1)->count() === $this->handTable->fresh()->tableSeats->where('can_continue', 1)->count()){
            return $this->continue();
        }

        // If the hand is completed start a new one...
        if($this->hand->fresh()->completed_on){
            return $this->start();
        }

        return [
            'gamePlay' => $this,
            'hand' => $this->hand->fresh(),
            'handTable' => $this->handTable->fresh(),
            'actions' => $this->hand->playerActions->fresh(),
            'streets' => $this->hand->fresh()->streets,
            'communityCards' => $this->getCommunityCards(),
            'wholeCards' => $this->getWholeCards(),
            'actionOn' => $this->getActionOn(),
            'players' => $this->getPlayerData(),
            'winner' => null
        ];
    }

    public function getActionOn()
    {

        $playerAfter = TableSeat::query()
            ->select('*')
            ->leftJoin('player_actions', 'table_seats.id', '=', 'player_actions.table_seat_id')
            ->where(function($query){
                $query->where('table_seats.table_id', $this->handTable->id);
                $query->where('table_seats.id', '>=',
                    $this->hand->playerActions
                        ->fresh()
                        ->sortBy([
                            ['id', 'desc'],
                            ['updated_at', 'desc']
                        ], SORT_NUMERIC)
                        ->first()->table_seat_id);
                $query->where('table_seats.can_continue', 0);
                $query->where('player_actions.active', 1);
            })
            ->first();

        if(!$playerAfter){
            return TableSeat::query()
                ->select('*')
                ->leftJoin('player_actions', 'table_seats.id', '=', 'player_actions.table_seat_id')
                ->where('table_seats.table_id', $this->handTable->id)
                ->where('table_seats.can_continue', 0)
                ->where('player_actions.active', 1)
                ->first();
        }

        return $playerAfter;

    }

    public function getPlayerData()
    {

        $playerData = [];
        $actionOn = false;
        foreach($this->hand->playerActions->fresh() as $playerAction){

            if($this->getActionOn()->player_id === $playerAction->player_id){
                $actionOn = true;
            }

            $playerData[] = [
                'action_id' => $playerAction->action_id,
                'player_id' => $playerAction->player_id,
                'table_seat_id' =>  $playerAction->table_seat_id,
                'hand_street_id' => $playerAction->hand_street_id,
                'bet_amount' => $playerAction->bet_amount,
                'active' => $playerAction->active,
                'can_continue' => $playerAction->tableSeat->can_continue,
                'whole_cards' => $this->getWholeCards($playerAction->player),
                'action_on' => $actionOn
            ];
        }

        return $playerData;
    }

    public function getWholeCards($player = null)
    {

        $wholeCards = [];

        if(isset($player)){
            foreach($player->wholeCards as $wholeCard){
                $wholeCards[] = [
                    'player_id' => $wholeCard->player_id,
                    'rank' => $wholeCard->card->rank->abbreviation,
                    'suit' => $wholeCard->card->suit->name
                ];
            }

            return $wholeCards;
        }

        foreach(TableSeat::where('can_continue', 1)->get() as $tableSeat){
            foreach($tableSeat->player->wholeCards->where('hand_id',$this->hand->fresh()->id) as $wholeCard){
                $wholeCards[] = [
                    'player_id' => $tableSeat->player->id,
                    'rank' => $wholeCard->card->rank->abbreviation,
                    'suit' => $wholeCard->card->suit->name
                ];
            }
        }

        return $wholeCards;
    }

    public function getCommunityCards()
    {
        $cards = [];
        foreach($this->hand->fresh()->streets as $street){
            foreach($street->cards as $streetCard){
                $cards[] = [
                    'rank' => $streetCard->card->rank->abbreviation,
                    'suit' => $streetCard->card->suit->name
                ];
            }
        }

        return $cards;
    }

    public function updateAllOtherSeatsBasedOnLatestAction()
    {
        // Update the table seat status of the latest action accordingly
        switch($this->hand->playerActions->fresh()->sortByDesc('updated_at')->first()->action_id){
            case 4:
            case 5:
                $canContinue = 0;
                break;
            default:
                break;
        }

        if(isset($canContinue)){
            TableSeat::query()
                ->where('table_id', $this->handTable->id)
                ->where('id', '!=', $this->hand->playerActions->fresh()->sortByDesc('updated_at')->first()->table_seat_id)
                ->update([
                    'can_continue' => $canContinue
                ]);
        }

    }

    public function updateSeatStatusOfLatestAction()
    {
        // Update the table seat status of the latest action accordingly
        switch($this->hand->playerActions->fresh()->sortByDesc('updated_at')->first()->action_id){
            case 2:
            case 3:
            case 4:
            case 5:
                $canContinue = 1;
                break;
            default:
                $canContinue = 0;
                break;
        }

        TableSeat::where('id', $this->hand->playerActions->fresh()->sortByDesc('updated_at')->first()->table_seat_id)
            ->update([
                'can_continue' => $canContinue
            ]);
    }

    public function initiateStreetActions()
    {
        $this->street = HandStreet::create([
            'street_id' => Street::where('name', 'Pre-flop')->first()->id,
            'hand_id' => $this->hand->id
        ]);

        foreach($this->handTable->tableSeats as $seat){
            $seat->player->actions()->create([
                'hand_street_id' => $this->street->id,
                'table_seat_id' => $seat->id,
                'hand_id' => $this->hand->id,
                'active' => 1
            ]);
        }

        return $this;
    }

    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    public function postBlinds()
    {

        // Small Blind
        PlayerAction::where([
            'player_id' =>  $this->handTable->tableSeats->slice(0, 1)->first()->player->id,
            'table_seat_id' =>  $this->handTable->tableSeats->slice(0, 1)->first()->id,
            'hand_street_id' => HandStreet::where([
                'street_id' => Street::where('name', $this->game->streets[0]['name'])->first()->id,
                'hand_id' => $this->hand->id
            ])->first()->id
        ])->update([
            'action_id' => Action::where('name', 'Bet')->first()->id, // Bet
            'bet_amount' => 25,
            'active' => 1,
        ]);

        TableSeat::where('id', $this->handTable->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        // Big Blind
        PlayerAction::where([
            'player_id' =>  $this->handTable->tableSeats->slice(1, 1)->first()->player->id,
            'table_seat_id' =>  $this->handTable->tableSeats->slice(1, 1)->first()->id,
            'hand_street_id' => HandStreet::where([
                'street_id' => Street::where('name', $this->game->streets[0]['name'])->first()->id,
                'hand_id' => $this->hand->id
            ])->first()->id
        ])->update([
            'action_id' => Action::where('name', 'Bet')->first()->id, // Bet
            'bet_amount' => 50,
            'active' => 1,
        ]);

        TableSeat::where('id', $this->handTable->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        return $this;

    }

    public function actionOn()
    {
        // return the PlayerAction record for this hand with, the lowest seat number, active 1 and can_continue 0
            // and the available actions

        return $this->actionOn;
    }

    public function currentStreet()
    {
        // Find out the latest HandStreet record for the $this->hand

        return $this->hand->streets->latest();
    }

    public function isLastStreet()
    {
        // if we are on the last street

        return true;
    }

    public function finish()
    {
        return 1;
    }

}

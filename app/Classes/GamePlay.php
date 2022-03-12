<?php

namespace App\Classes;

use App\Models\HandStreet;
use App\Models\PlayerAction;
use App\Models\Table;
use App\Models\Hand;
use App\Models\TableSeat;

class GamePlay
{
    protected $game;
    protected $dealer;
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

        // If all active players can_continue
        if($this->hand->fresh()->actions->where('active', 1)->count() === $this->handTable->fresh()->tableSeats->where('can_continue', 1)->count()){
            return $this->continue();
        }

        // Else if latest hand is completed start a new one...
        if($this->hand->fresh()->completed_on){
            return $this->start();
        }

        return [
            'hand' => $this->hand->fresh(),
            'handTable' => $this->handTable->fresh(),
            'actions' => $this->hand->actions->fresh()
        ];

    }

    public function continue()
    {

        // Not keen on the way I'm adding/subtracting from the handStreets->count() to match array starting with 0
        $this->street = HandStreet::create([
            'street_id' => $this->hand->fresh()->streets->count() + 1,
            'hand_id' => $this->hand->id
        ]);

        $dealtCards = 0;
        while($dealtCards < $this->game->streets[$this->hand->fresh()->streets->count() - 1]['community_cards']){
            $this->dealer->dealStreetCard($this->street);
            $dealtCards++;
        }

        return [
            'hand' => $this->hand->fresh(),
            'handTable' => $this->handTable->fresh(),
            'actions' => $this->hand->actions->fresh()
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
            'hand' => $this->hand,
            'handTable' => $this->handTable,
            'actions' => $this->hand->actions
        ];

    }

    public function updateAllOtherSeatsBasedOnLatestAction()
    {
        // Update the table seat status of the latest action accordingly
        switch($this->hand->actions->fresh()->sortByDesc('updated_at')->first()->action_id){
            case 4:
            case 5:
                $canContinue = 0;
                break;
            default:
                $canContinue = 1;
                break;
        }

        TableSeat::query()
            ->where('table_id', $this->handTable->id)
            ->where('id', '!=', $this->hand->actions->fresh()->sortByDesc('updated_at')->first()->table_seat_id)
            ->update([
                'can_continue' => $canContinue
            ]);
    }

    public function updateSeatStatusOfLatestAction()
    {
        // Update the table seat status of the latest action accordingly
        switch($this->hand->actions->fresh()->sortByDesc('updated_at')->first()->action_id){
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

        TableSeat::where('id', $this->hand->actions->fresh()->sortByDesc('updated_at')->first()->table_seat_id)
            ->update([
                'can_continue' => $canContinue
            ]);
    }

    public function initiateStreetActions()
    {
        $this->street = HandStreet::create([
            'street_id' => 1,
            'hand_id' => $this->hand->id
        ]);

        foreach($this->handTable->tableSeats as $seat){
            $seat->player->actions()->create([
                'hand_street_id' => $this->street->id,
                'table_seat_id' => $seat->id,
                'hand_id' => $this->hand->id
            ]);

            $seat->update([
                'active' => 1
            ]);
        }
    }

    public function postBlinds()
    {
        // Small Blind
        PlayerAction::where([
            'player_id' =>  $this->handTable->tableSeats->slice(0, 1)->first()->player->id,
            'table_seat_id' =>  $this->handTable->tableSeats->slice(0, 1)->first()->id,
            'hand_street_id' => $this->street->id,
        ])->update([
            'action_id' => 4, // Bet
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
            'hand_street_id' => $this->street->id,
        ])->update([
            'action_id' => 4, // Bet
            'bet_amount' => 50,
            'active' => 1,
        ]);

        TableSeat::where('id', $this->handTable->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

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
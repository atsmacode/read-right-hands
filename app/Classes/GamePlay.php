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
        $this->handTable = Table::query()->first();
        $this->players = $hand->handTable->players->where('active', 1)->all();
        $this->street = null;

    }

    public function play()
    {
        return $this->start();
    }

    public function start()
    {

        $this->initiateStreetActions();

        $this->postBlinds();

        $this->dealer->setDeck()->shuffle();

        if($this->game->streets[0]['whole_cards']){
            $dealtCards = 0;
            while($dealtCards < $this->game[0]['whole_cards']){
                $this->dealer->dealTo($this->players);
                $dealtCards++;
            }
        }

        return [
            'player_actions' => $this->hand->actions
        ];

    }

    public function initiateStreetActions()
    {
        $this->street = HandStreet::create([
            'street_id' => 1,
            'hand_id' => $this->hand->id
        ]);

        foreach($this->handTable->tableSeats as $seat){
            $seat->player->actions()->create([
                'hand_street_id' => $this->street->id
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
            'bet_amount' => 25
        ]);

        TableSeat::query()->find($this->handTable->tableSeats->slice(0, 1)->first()->id)
            ->update([
            'active' => 1,
            'can_continue' => 1
        ]);

        // Big Blind
        PlayerAction::where([
            'player_id' =>  $this->handTable->tableSeats->slice(1, 1)->first()->player->id,
            'table_seat_id' =>  $this->handTable->tableSeats->slice(1, 1)->first()->id,
            'hand_street_id' => $this->street->id,
        ])->update([
            'action_id' => 4, // Bet
            'bet_amount' => 50
        ]);

        TableSeat::query()->find($this->handTable->tableSeats->slice(1, 1)->first()->id)
            ->update([
            'active' => 1,
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

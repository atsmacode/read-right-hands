<?php

namespace App\Classes;

use App\Models\Table;
use App\Models\Hand;

class GamePlay
{
    protected $game;
    protected $dealer;
    public $actionOn;

    public function __construct(Game $game, Dealer $dealer, Hand $hand, Table $table, $players)
    {
        $this->game = $game;
        $this->dealer = $dealer;
        $this->hand = $hand;
        $this->players = $players;
        $this->table = $table;
    }

    public function start()
    {

        // Record 1st HandStreet for the game type

        $this->dealer->setDeck()->shuffle();

        $dealtCards = 0;
        while($dealtCards < $this->game->wholeCards){
            $this->dealer->setDeck()->dealTo($this->players);
            $dealtCards++;
        }

        // For each table seat
            // Record PLayerAction record
            // action_id null
            // active 1
            // can_continue 0

        // Update SB/BB PlayerAction records

        return $this->actionOn();

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

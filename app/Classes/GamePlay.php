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
    protected $fold;
    protected $check;
    protected $call;
    protected $bet;
    protected $raise;

    public function __construct(Hand $hand, $deck = null)
    {
        $this->game = new PotLimitHoldEm();
        $this->dealer = (new Dealer())->setDeck($deck);
        $this->hand = $hand;
        $this->handTable = Table::first();
        $this->street = null;
        $this->fold = Action::where('name', 'Fold')->first();
        $this->check = Action::where('name', 'Check')->first();
        $this->call = Action::where('name', 'Call')->first();
        $this->bet = Action::where('name', 'Bet')->first();
        $this->raise = Action::where('name', 'Raise')->first();
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
            'deck' => $this->dealer->getDeck(),
            'hand' => $this->hand->fresh(),
            'handTable' => $this->handTable->fresh(),
            'actions' => $this->hand->playerActions->fresh(),
            'streets' => $this->hand->fresh()->streets,
            'communityCards' => $this->getCommunityCards(),
            'players' => $this->getPlayerData(),
            'winner' => (new Showdown($this->hand->fresh()))->compileHands()->decideWinner()
        ];
    }

    public function continue()
    {

        $this->updatePlayerStatusesOnNewStreet();

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
            'deck' => $this->dealer->getDeck(),
            'hand' => $this->hand->fresh(),
            'handTable' => $this->handTable->fresh(),
            'actions' => $this->hand->playerActions->fresh(),
            'streets' => $this->hand->fresh()->streets,
            'communityCards' => $this->getCommunityCards(),
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
            'deck' => $this->dealer->getDeck(),
            'hand' => $this->hand->fresh(),
            'handTable' => $this->handTable->fresh(),
            'actions' => $this->hand->playerActions->fresh(),
            'streets' => $this->hand->streets->fresh(),
            'communityCards' => $this->getCommunityCards(),
            'players' => $this->getPlayerData(),
            'winner' => null
        ];

    }

    public function nextStep()
    {

        if($this->readyForShowdown() || $this->onePlayerRemainsThatCanContinue()){
            return $this->showdown();
        }

        if($this->allActivePlayersCanContinue()){
            return $this->continue();
        }

        if($this->theLastHandWasCompleted()){
            return $this->start();
        }

        return [
            'gamePlay' => $this,
            'deck' => $this->dealer->getDeck(),
            'hand' => $this->hand->fresh(),
            'handTable' => $this->handTable->fresh(),
            'actions' => $this->hand->playerActions->fresh(),
            'streets' => $this->hand->fresh()->streets,
            'communityCards' => $this->getCommunityCards(),
            'players' => $this->getPlayerData(),
            'winner' => null
        ];
    }

    protected function updatePlayerStatusesOnNewStreet()
    {
        /*
         * Reset can_continue & BB status once pre-flop action and/or previous street is finished.
         */
        TableSeat::query()
            ->where('table_id', $this->handTable->fresh()->id)
            ->update([
                'can_continue' => 0
            ]);

        if($this->hand->fresh()->streets->count() === 1){
            PlayerAction::query()
                ->where('hand_id', $this->hand->fresh()->id)
                ->where('big_blind', 1)
                ->update([
                    'big_blind' => 0
                ]);
        }

        /*
         * Always reset action_id.
         */
        PlayerAction::query()
            ->where('hand_id', $this->hand->fresh()->id)
            ->update([
                'action_id' => null
            ]);
    }

    protected function readyForShowdown()
    {
        return $this->hand->fresh()->streets->count() === count($this->game->streets) &&
            $this->hand->fresh()->playerActions->where('active', 1)->count() ===
            $this->handTable->fresh()->tableSeats->where('can_continue', 1)->count();
    }

    protected function onePlayerRemainsThatCanContinue()
    {
        return $this->hand->fresh()->playerActions->where('active', 1)->count()
            === $this->handTable->fresh()->tableSeats->where('can_continue', 1)->count()
            && $this->handTable->fresh()->tableSeats->where('can_continue', 1)->count() === 1;
    }

    protected function allActivePlayersCanContinue()
    {
        return $this->hand->fresh()->playerActions->where('active', 1)->count() ===
            $this->handTable->fresh()->tableSeats->where('can_continue', 1)->count();
    }

    protected function theLastHandWasCompleted()
    {
        return $this->hand->fresh()->completed_on;
    }

    public function getActionOn()
    {

        if(!$this->hand->playerActions->fresh()->whereNotNull('action_id')->first()){
            return TableSeat::query()
                ->select('table_seats.*')
                ->leftJoin('player_actions', 'table_seats.id', '=', 'player_actions.table_seat_id')
                ->where('table_seats.table_id', $this->handTable->fresh()->id)
                ->where('table_seats.id', $this->hand->fresh()->playerActions->where('active', 1)->first()->table_seat_id)
                ->first();
        }

        $lastToAct = $this->hand->playerActions
            ->fresh()
            ->sortBy([
                ['updated_at', 'desc']
            ], SORT_NUMERIC)
            ->first()
            ->table_seat_id;

        $playerAfter = $this->hand->playerActions->fresh()->where('active', 1)->where('table_seat_id', '>', $lastToAct)->first()
            ? $this->hand->playerActions->fresh()->where('active', 1)->where('table_seat_id', '>', $lastToAct)->first()->tableSeat
            : null;

        if(!$playerAfter){
            return TableSeat::query()
                ->select('table_seats.*')
                ->leftJoin('player_actions', 'table_seats.id', '=', 'player_actions.table_seat_id')
                ->where('table_seats.table_id', $this->handTable->fresh()->id)
                ->where('table_seats.id', $this->hand->fresh()->playerActions->where('active', 1)->first()->table_seat_id)
                ->first();
        }

        return $playerAfter;

    }

    public function getPlayerData()
    {

        $playerData = [];

        foreach($this->hand->playerActions->fresh() as $playerAction){

            $actionOn = false;

            if($this->getActionOn() && $this->getActionOn()->player_id === $playerAction->player_id){
                $actionOn = true;
            }

            $actionName = $playerAction->action ? $playerAction->action->name : null;

            $playerData[] = [
                'name' => $playerAction->player->name,
                'action_id' => $playerAction->action_id,
                'action_name' => $actionName ,
                'player_id' => $playerAction->player_id,
                'table_seat_id' =>  $playerAction->table_seat_id,
                'hand_street_id' => $playerAction->hand_street_id,
                'bet_amount' => $playerAction->bet_amount,
                'active' => $playerAction->active,
                'can_continue' => $playerAction->tableSeat->can_continue,
                'whole_cards' => $this->getWholeCards($playerAction->player),
                'action_on' => $actionOn,
                'availableOptions' => $this->getAvailableOptionsBasedOnLatestAction($playerAction)
            ];
        }

        return $playerData;
    }

    public function getWholeCards($player = null)
    {

        $wholeCards = [];

        if(isset($player)){
            foreach($player->wholeCards->where('hand_id', $this->hand->fresh()->id) as $wholeCard){
                $wholeCards[] = [
                    'player_id' => $wholeCard->player_id,
                    'rank' => $wholeCard->card->rank->abbreviation,
                    'suit' => $wholeCard->card->suit->name,
                    'suitAbbreviation' => $wholeCard->card->suit->abbreviation
                ];
            }

            return $wholeCards;
        }

        foreach(TableSeat::where('can_continue', 1)->get() as $tableSeat){
            foreach($tableSeat->player->wholeCards->where('hand_id',$this->hand->fresh()->id) as $wholeCard){
                $wholeCards[] = [
                    'player_id' => $tableSeat->player->id,
                    'rank' => $wholeCard->card->rank->abbreviation,
                    'suit' => $wholeCard->card->suit->name,
                    'suitAbbreviation' => $wholeCard->card->suit->abbreviation
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
                    'suit' => $streetCard->card->suit->name,
                    'suitAbbreviation' => $streetCard->card->suit->abbreviation
                ];
            }
        }

        return $cards;
    }

    public function getAvailableOptionsBasedOnLatestAction($playerAction)
    {

        $options = [];

        /*
         * We only need to update the available actions if a player did something other than fold.
         */
        $latestAction = $this->hand->playerActions
            ->fresh()
            ->whereNotIn('action_id', [
                $this->fold->id
            ])
            ->sortBy([
                ['updated_at', 'desc']
            ], SORT_NUMERIC)
            ->first();

        if($playerAction->active === 1){

            $options = [
                $this->fold
            ];

            switch($latestAction->action_id){
                case $this->call->id:
                    /*
                     * BB can only check if there were no raises before the latest call action.
                     */
                    if(
                        $playerAction->big_blind === 1 &&
                        !$this->hand->playerActions->fresh()->whereIn('action_id', $this->raise->id)->first()
                    ){
                        array_push($options, $this->check, $this->raise);
                    } else {
                        array_push($options, $this->call, $this->raise);
                    }
                    break;
                case $this->bet->id:
                case $this->raise->id:
                    array_push($options, $this->call, $this->raise);
                    break;
                case $this->check->id:
                default:
                    array_push($options, $this->check, $this->bet);
                    break;
            }

        }

        return collect($options);
    }

    public function updateAllOtherSeatsBasedOnLatestAction()
    {

        $latestAction = $this->hand->playerActions
            ->fresh()
            ->sortBy([
                ['updated_at', 'desc']
            ], SORT_NUMERIC)
            ->first();

        // Update the other table seat statuses accordingly
        switch($latestAction->action_id){
            case $this->bet->id:
            case $this->raise->id:
                $canContinue = 0;
                break;
            default:
                break;
        }

        if(isset($canContinue)){
            TableSeat::query()
                ->where('table_id', $this->handTable->id)
                ->where('id', '!=', $latestAction->table_seat_id)
                ->update([
                    'can_continue' => $canContinue
                ]);
        }

    }

    public function updateSeatStatusOfLatestAction()
    {

        $latestAction = $this->hand->playerActions
            ->fresh()
            ->sortBy([
                ['updated_at', 'desc']
            ], SORT_NUMERIC)
            ->first();

        // Update the table seat status of the latest action accordingly
        switch($latestAction->action_id){
            case $this->check->id:
            case $this->call->id:
            case $this->bet->id:
            case $this->raise->id:
                $canContinue = 1;
                break;
            default:
                $canContinue = 0;
                break;
        }

        TableSeat::where('id', $latestAction->table_seat_id)
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

            /*
             * For testing so I can get the latest action, otherwise they are all the same
             */
            PlayerAction::where([
                'hand_street_id' => $this->street->id,
                'table_seat_id' => $seat->id,
                'hand_id' => $this->hand->id,
            ])->update([
                'updated_at' => date('Y-m-d H:i:s', strtotime('-15 seconds'))
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
            'updated_at' => date('Y-m-d H:i:s', strtotime('- 10 seconds')) // For testing so I can get the latest action, otherwise they are all the same
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
            'big_blind' => 1,
            'updated_at' => date('Y-m-d H:i:s', strtotime('- 5 seconds')) // For testing so I can get the latest action, otherwise they are all the same
        ]);

        TableSeat::where('id', $this->handTable->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        return $this;

    }

}

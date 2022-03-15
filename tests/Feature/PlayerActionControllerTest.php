<?php

namespace Tests\Feature;

use App\Classes\GamePlay;
use App\Models\Action;
use App\Models\Hand;
use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\TableSeat;
use Tests\Unit\TestEnvironment;

class PlayerActionControllerTest extends TestEnvironment
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->gamePlay = new GamePlay(Hand::create());

        $this->player1 = Player::factory()->create();
        $this->player2 = Player::factory()->create();
        $this->player3 = Player::factory()->create();

        TableSeat::factory([
            'table_id' => $this->gamePlay->handTable->id,
            'player_id' => $this->player1->id
        ])->create();

        TableSeat::factory([
            'table_id' => $this->gamePlay->handTable->id,
            'player_id' => $this->player2->id
        ])->create();

        TableSeat::factory([
            'table_id' => $this->gamePlay->handTable->id,
            'player_id' => $this->player3->id
        ])->create();

    }

    /**
     * @test
     * @return void
     */
    public function an_action_can_be_taken()
    {

        $gameData = $this->gamePlay->start();

        $response = $this->post('action', [
            'game_play' => $gameData['gamePlay'],
            'player_id' =>  $gameData['actions'][2]->player_id,
            'table_seat_id' =>  $gameData['actions'][2]->table_seat_id,
            'hand_street_id' => $gameData['actions'][2]->hand_street_id,
            'action_id' => Action::where('name', 'Call')->first()->id,
            'bet_amount' => 50.0,
            'active' => 1
        ]);

        $response->assertStatus(200);

    }

    /**
     * @test
     * @return void
     */
    public function a_flop_will_be_dealt_once_all_active_players_can_continue()
    {

        $gameData = $this->gamePlay->start();

        $this->assertCount(1, $gameData['streets']);

        $this->executeActions($gameData);

        $response = $this->post('action', [
            'game_play' => $gameData['gamePlay'],
            'player_id' =>  $gameData['actions'][1]->player_id,
            'table_seat_id' =>  $gameData['actions'][1]->table_seat_id,
            'hand_street_id' => $gameData['actions'][1]->hand_street_id,
            'action_id' => Action::where('name', 'Check')->first()->id,
            'bet_amount' => null,
            'active' => 1
        ]);

        $response->assertStatus(200);

        $this->assertCount(2, $response['streets']);
        $this->assertCount(3, $response['cards']);


    }

    protected function executeActions($response)
    {
        // Player 3 Calls BB
        PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(2, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 1 Folds
        PlayerAction::where('id', $response['actions']->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Fold')->first()->id,
                'bet_amount' => null,
                'active' => 0
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

    }
}

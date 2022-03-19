<?php

namespace Tests\Feature;

use App\Classes\GamePlay;
use App\Models\Action;
use App\Models\Hand;
use App\Models\HandStreet;
use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\Street;
use App\Models\Table;
use App\Models\TableSeat;
use Tests\Unit\TestEnvironment;

class PlayerActionControllerTest extends TestEnvironment
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->gamePlay = new GamePlay(Hand::create(), Table::create(['name' => 'Table 1', 'seats' => 3]));

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
            'hand_id' => $gameData['hand']->id,
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
            'hand_id' => $gameData['hand']->id,
            'player_id' =>  $gameData['actions'][1]->player_id,
            'table_seat_id' =>  $gameData['actions'][1]->table_seat_id,
            'hand_street_id' => $gameData['actions'][1]->hand_street_id,
            'action_id' => Action::where('name', 'Check')->first()->id,
            'bet_amount' => null,
            'active' => 1
        ]);

        $response->assertStatus(200);

        $response->assertJsonCount(2, 'streets');
        $this->assertCount(2, $response['streets']);
        $this->assertCount(3, $response['communityCards']);

    }

    /**
     * @test
     * @return void
     */
    public function a_turn_will_be_dealt_once_all_active_players_can_continue()
    {

        $gameData = $this->gamePlay->start();

        $gameData = $this->setFlop($gameData);

        $this->assertCount(2, $gameData['gamePlay']->hand->fresh()->streets);

        $this->executeActions($gameData);

        $response = $this->post('action', [
            'game_play' => $gameData['gamePlay'],
            'hand_id' => $gameData['hand']->id,
            'player_id' =>  $gameData['actions'][1]->player_id,
            'table_seat_id' =>  $gameData['actions'][1]->table_seat_id,
            'hand_street_id' => $gameData['actions'][1]->hand_street_id,
            'action_id' => Action::where('name', 'Check')->first()->id,
            'bet_amount' => null,
            'active' => 1
        ]);

        $response->assertStatus(200);

        $this->assertCount(3, $response['streets']);
        $this->assertCount(4, $response['communityCards']);

    }

    /**
     * @test
     * @return void
     */
    public function a_river_will_be_dealt_once_all_active_players_can_continue()
    {

        $gameData = $this->gamePlay->start();

        $gameData = $this->setFlop($gameData);
        $gameData = $this->setTurn($gameData);

        $this->assertCount(3, $gameData['gamePlay']->hand->fresh()->streets);

        $this->executeActions($gameData);

        $response = $this->post('action', [
            'game_play' => $gameData['gamePlay'],
            'hand_id' => $gameData['hand']->id,
            'player_id' =>  $gameData['actions'][1]->player_id,
            'table_seat_id' =>  $gameData['actions'][1]->table_seat_id,
            'hand_street_id' => $gameData['actions'][1]->hand_street_id,
            'action_id' => Action::where('name', 'Check')->first()->id,
            'bet_amount' => null,
            'active' => 1
        ]);

        $response->assertStatus(200);

        $this->assertCount(4, $response['streets']);
        $this->assertCount(5, $response['communityCards']);

    }

    /**
     * @test
     * @return void
     */
    public function a_winner_will_be_decided_and_hand_set_to_completed_once_all_active_players_can_continue_on_the_river()
    {

        $gameData = $this->gamePlay->start();

        $gameData = $this->setFlop($gameData);
        $gameData = $this->setTurn($gameData);
        $gameData = $this->setRiver($gameData);

        $this->assertCount(4, $gameData['gamePlay']->hand->fresh()->streets);

        $this->executeActions($gameData);

        $response = $this->post('action', [
            'game_play' => $gameData['gamePlay'],
            'hand_id' => $gameData['hand']->id,
            'player_id' =>  $gameData['actions'][1]->player_id,
            'table_seat_id' =>  $gameData['actions'][1]->table_seat_id,
            'hand_street_id' => $gameData['actions'][1]->hand_street_id,
            'action_id' => Action::where('name', 'Check')->first()->id,
            'bet_amount' => null,
            'active' => 1
        ]);

        $response->assertStatus(200);

        dump($response['communityCards']);
        dump('Winner: ' . $response['winner']['player']['id']);
        dump($response['winner']['handType']);

        $this->assertNotNull($response['hand']['completed_on']);
        $this->assertNotNull($response['winner']);

    }

    protected function setFlop($gameData)
    {
        // Manually set the flop
        $flop = HandStreet::create([
            'street_id' => Street::where('name', 'Flop')->first()->id,
            'hand_id' => $gameData['hand']->id
        ]);

        $dealtCards = 0;
        while($dealtCards < $gameData['gamePlay']->game->streets[1]['community_cards']){
            $gameData['gamePlay']->dealer->dealStreetCard($flop);
            $dealtCards++;
        }

        return $gameData;
    }

    protected function setTurn($gameData)
    {
        // Manually set the turn
        $turn = HandStreet::create([
            'street_id' => Street::where('name', 'Turn')->first()->id,
            'hand_id' => $gameData['hand']->id
        ]);

        $dealtCards = 0;
        while($dealtCards < $gameData['gamePlay']->game->streets[2]['community_cards']){
            $gameData['gamePlay']->dealer->dealStreetCard($turn);
            $dealtCards++;
        }

        return $gameData;
    }

    protected function setRiver($gameData)
    {
        // Manually set the river
        $river = HandStreet::create([
            'street_id' => Street::where('name', 'River')->first()->id,
            'hand_id' => $gameData['hand']->id
        ]);

        $dealtCards = 0;
        while($dealtCards < $gameData['gamePlay']->game->streets[3]['community_cards']){
            $gameData['gamePlay']->dealer->dealStreetCard($river);
            $dealtCards++;
        }

        return $gameData;
    }

    protected function executeActions($response)
    {
        // Player 3 Calls BB
        PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1,
                'updated_at' => date('Y-m-d H:i:s', strtotime('- 10 seconds'))
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
                'active' => 0,
                'updated_at' => date('Y-m-d H:i:s', strtotime('- 5 seconds'))
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

    }
}

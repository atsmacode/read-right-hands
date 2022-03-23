<?php

namespace Tests\Feature;

use App\Classes\GamePlay;
use App\Helpers\BetHelper;
use App\Models\Action;
use App\Models\Hand;
use App\Models\HandStreet;
use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\Stack;
use App\Models\Street;
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

        $this->gamePlay->start();

        $response = $this->post('action', [
            'hand_id' => $this->gamePlay->hand->fresh()->id,
            'player_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->player_id,
            'table_seat_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->table_seat_id,
            'hand_street_id' => $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->hand_street_id,
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

        $this->gamePlay->start();

        $this->assertCount(1, $this->gamePlay->hand->fresh()->streets);

        $this->executeActions();

        $response = $this->post('action', [
            'hand_id' => $this->gamePlay->hand->fresh()->id,
            'player_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->player_id,
            'table_seat_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->table_seat_id,
            'hand_street_id' => $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->hand_street_id,
            'action_id' => Action::where('name', 'Check')->first()->id,
            'bet_amount' => null,
            'active' => 1
        ]);

        $response->assertStatus(200);

        $this->assertCount(2, $this->gamePlay->hand->fresh()->streets);
        $this->assertCount(3, $response['communityCards']);

    }

    /**
     * @test
     * @return void
     */
    public function a_turn_will_be_dealt_once_all_active_players_can_continue()
    {

        $this->gamePlay->start();

        $this->setFlop();

        $this->assertCount(2, $this->gamePlay->hand->fresh()->fresh()->streets);

        $this->executeActions();

        $response = $this->post('action', [
            'hand_id' => $this->gamePlay->hand->fresh()->id,
            'player_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->player_id,
            'table_seat_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->table_seat_id,
            'hand_street_id' => $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->hand_street_id,
            'action_id' => Action::where('name', 'Check')->first()->id,
            'bet_amount' => null,
            'active' => 1
        ]);

        $response->assertStatus(200);

        $this->assertCount(3, $this->gamePlay->hand->fresh()->streets);
        $this->assertCount(4, $response['communityCards']);

    }

    /**
     * @test
     * @return void
     */
    public function a_river_will_be_dealt_once_all_active_players_can_continue()
    {

        $this->gamePlay->start();

        $this->setFlop();
        $this->setTurn();

        $this->assertCount(3, $this->gamePlay->hand->fresh()->fresh()->streets);

        $this->executeActions();

        $response = $this->post('action', [
            'hand_id' => $this->gamePlay->hand->fresh()->id,
            'player_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->player_id,
            'table_seat_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->table_seat_id,
            'hand_street_id' => $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->hand_street_id,
            'action_id' => Action::where('name', 'Check')->first()->id,
            'bet_amount' => null,
            'active' => 1
        ]);

        $response->assertStatus(200);

        $this->assertCount(4, $this->gamePlay->hand->fresh()->streets);
        $this->assertCount(5, $response['communityCards']);

    }

    /**
     * @test
     * @return void
     */
    public function a_winner_will_be_decided_and_hand_set_to_completed_once_all_active_players_can_continue_on_the_river()
    {

        $this->gamePlay->start();

        $this->setFlop();
        $this->setTurn();
        $this->setRiver();

        $this->assertCount(4, $this->gamePlay->hand->fresh()->fresh()->streets);

        $this->executeActions();

        $response = $this->post('action', [
            'hand_id' => $this->gamePlay->hand->fresh()->id,
            'player_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->player_id,
            'table_seat_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->table_seat_id,
            'hand_street_id' => $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->hand_street_id,
            'action_id' => Action::where('name', 'Check')->first()->id,
            'bet_amount' => null,
            'active' => 1
        ]);

        $response->assertStatus(200);

        $this->assertNotNull($this->gamePlay->hand->fresh()->completed_on);
        $this->assertNotNull($response['winner']);

    }

    /**
     * @test
     * @return void
     */
    public function the_big_blind_will_win_the_pot_if_all_other_players_fold_pre_flop()
    {
        $this->gamePlay->start();

        $this->assertCount(1, $this->gamePlay->hand->fresh()->streets->fresh());

        $player1 = PlayerAction::where(
            'id',
            $this->gamePlay->hand->playerActions->fresh()->slice(0, 1)->first()->id
        )->first();

        $player1->action_id = Action::where('name', 'Fold')->first()->id;
        $player1->bet_amount = null;
        $player1->active = 0;
        $player1->updated_at = date('Y-m-d H:i:s', strtotime('- 2 seconds'));
        $player1->save();

        TableSeat::query()->where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        $response = $this->post('action', [
            'hand_id' => $this->gamePlay->hand->fresh()->id,
            'player_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(1, 1)->first()->player_id,
            'table_seat_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(1, 1)->first()->table_seat_id,
            'hand_street_id' => $this->gamePlay->hand->playerActions->fresh()->slice(1, 1)->first()->hand_street_id,
            'action_id' => Action::where('name', 'Fold')->first()->id,
            'bet_amount' => null,
            'active' => 0
        ]);

        $response->assertStatus(200);

        $this->assertCount(1, $this->gamePlay->hand->fresh()->streets->fresh());
        $this->assertEquals(1, $response['players'][2]['can_continue']);
        $this->assertEquals($this->player3->id, $response['winner']['player']['id']);

    }

    /**
     * @test
     * @return void
     */
    public function a_bet_after_the_blinds_will_be_added_to_the_pot_and_deducted_from_the_stack()
    {
        $this->gamePlay->start();

        $this->assertEquals(75, $this->gamePlay->hand->pot->amount);

        $player1 = $this->gamePlay->hand->playerActions->fresh()->slice(0, 1)->first();

        $response = $this->post('action', [
            'hand_id' => $this->gamePlay->hand->fresh()->id,
            'player_id' =>  $player1->player_id,
            'table_seat_id' =>  $player1->table_seat_id,
            'hand_street_id' => $player1->hand_street_id,
            'action_id' => Action::where('name', 'Call')->first()->id,
            'bet_amount' => 50.0,
            'active' => 1
        ]);

        $response->assertStatus(200);

        $this->assertEquals(125, $this->gamePlay->hand->fresh()->pot->amount);

        $this->assertEquals(950, $player1->fresh()->player->stacks->where('table_id', $this->gamePlay->handTable->id)->first()->amount);

    }

    /**
     * @test
     * @return void
     */
    public function the_pot_will_be_awarded_to_the_winner_of_the_hand()
    {
        $this->gamePlay->start();

        $this->assertEquals(75, $this->gamePlay->hand->pot->amount);

        $this->setFlop();
        $this->setTurn();
        $this->setRiver();

        $player1 = PlayerAction::where(
            'id',
            $this->gamePlay->hand->playerActions->fresh()->slice(0, 1)->first()->id
        )->first();

        $player1->action_id = Action::where('name', 'Call')->first()->id;
        $player1->bet_amount = 50.0;
        $player1->active = 1;
        $player1->updated_at = date('Y-m-d H:i:s', strtotime('- 2 seconds'));
        $player1->save();

        TableSeat::query()->where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        BetHelper::handle($this->gamePlay->hand, $player1->player, $player1->bet_amount);

        $this->assertEquals(125, $this->gamePlay->hand->pot->amount);

        $player2 = PlayerAction::where(
            'id',
            $this->gamePlay->hand->playerActions->fresh()->slice(1, 1)->first()->id
        )->first();

        $player2->action_id = Action::where('name', 'Fold')->first()->id;
        $player2->bet_amount = null;
        $player2->active = 0;
        $player2->updated_at = date('Y-m-d H:i:s', strtotime('- 2 seconds'));
        $player2->save();

        TableSeat::query()->where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        BetHelper::handle($this->gamePlay->hand, $player2->player, $player2->bet_amount);

        $this->assertEquals(125, $this->gamePlay->hand->pot->amount);

        $response = $this->post('action', [
            'hand_id' => $this->gamePlay->hand->fresh()->id,
            'player_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->player_id,
            'table_seat_id' =>  $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->table_seat_id,
            'hand_street_id' => $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->hand_street_id,
            'action_id' => Action::where('name', 'Check')->first()->id,
            'bet_amount' => null,
            'active' => 1
        ]);

        $response->assertStatus(200);

        $winnerId = $response['winner']['player']['id'];

        $this->assertEquals(1075, Stack::where([
            'player_id' => $winnerId,
            'table_id' => $this->gamePlay->handTable->id
        ])->first()->amount);

    }

    protected function setFlop()
    {
        // Manually set the flop
        $flop = HandStreet::create([
            'street_id' => Street::where('name', 'Flop')->first()->id,
            'hand_id' => $this->gamePlay->hand->fresh()->id
        ]);

        $this->gamePlay->dealer->dealStreetCards(
            $flop,
            $this->gamePlay->game->streets[1]['community_cards']
        );

    }

    protected function setTurn()
    {
        // Manually set the turn
        $turn = HandStreet::create([
            'street_id' => Street::where('name', 'Turn')->first()->id,
            'hand_id' => $this->gamePlay->hand->fresh()->id
        ]);

        $this->gamePlay->dealer->dealStreetCards(
            $turn,
            $this->gamePlay->game->streets[2]['community_cards']
        );

    }

    protected function setRiver()
    {
        // Manually set the river
        $river = HandStreet::create([
            'street_id' => Street::where('name', 'River')->first()->id,
            'hand_id' => $this->gamePlay->hand->fresh()->id
        ]);

        $this->gamePlay->dealer->dealStreetCards(
            $river,
            $this->gamePlay->game->streets[3]['community_cards']
        );

    }

    protected function executeActions()
    {
        // Player 1 Calls BB
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1,
                'updated_at' => date('Y-m-d H:i:s', strtotime('- 10 seconds'))
            ]);

        TableSeat::where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 2 Folds
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(1, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Fold')->first()->id,
                'bet_amount' => null,
                'active' => 0,
                'updated_at' => date('Y-m-d H:i:s', strtotime('- 5 seconds'))
            ]);

        TableSeat::where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

    }
}

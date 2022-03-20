<?php

namespace Tests\Unit;

use App\Classes\GamePlay;
use App\Models\Action;
use App\Models\Hand;
use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\TableSeat;

class GamePlayTest extends TestEnvironment
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->gamePlay = new GamePlay(Hand::create());

        $this->player1 = Player::factory()->create();
        $this->player2 = Player::factory()->create();
        $this->player3 = Player::factory()->create();
        $this->player4 = Player::factory()->create();

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

        TableSeat::factory([
            'table_id' => $this->gamePlay->handTable->id,
            'player_id' => $this->player4->id
        ])->create();

    }

    /**
     * @test
     * @return void
     */
    public function it_can_start_the_game()
    {
        $response = $this->gamePlay->start();

        // There are 4 players at the table
        $this->assertCount(4, $response['actions']);

        // The small blind was posted
        $this->assertEquals(25.0, $response['actions']->slice(1, 1)->first()->bet_amount);
        $this->assertEquals('Bet', $response['actions']->slice(1, 1)->first()->action->name);

        // The big blind was posted
        $this->assertEquals(50.0, $response['actions']->slice(2, 1)->first()->bet_amount);
        $this->assertEquals('Bet', $response['actions']->slice(2, 1)->first()->action->name);

        // The last player at the table has not acted yet
        $this->assertEquals(null, $response['actions']->slice(3, 1)->first()->bet_amount);
        $this->assertEquals(null, $response['actions']->slice(3, 1)->first()->action_id);

        // Each player in the hand has 2 whole cards
        foreach($response['handTable']->players as $player){
            $this->assertCount(2, $player->wholeCards->where('hand_id', $response['hand']->id));
        }

        // the_action_will_be_on_the_player_after_the_big_blind_once_a_hand_is_started
        $this->assertTrue($response['players'][3]['action_on']);

    }

    /**
     * @test
     * @return void
     */
    public function it_can_deal_a_new_street()
    {
        $response = $this->gamePlay->start();

        $player4 = PlayerAction::where('id', $response['actions']->slice(3, 1)->first()->id)->first();
        $player4->action_id = Action::where('name', 'Call')->first()->id;
        $player4->bet_amount = 50.0;
        $player4->active = 1;
        $player4->updated_at = date('Y-m-d H:i:s', strtotime('- 8 seconds'));
        $player4->save();

        TableSeat::where('id', $response['handTable']->tableSeats->slice(3, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        $player1 = PlayerAction::where('id', $response['actions']->slice(0, 1)->first()->id)->first();
        $player1->action_id = Action::where('name', 'Call')->first()->id;
        $player1->bet_amount = 50.0;
        $player1->active = 1;
        $player1->updated_at = date('Y-m-d H:i:s', strtotime('- 8 seconds'));
        $player1->save();

        TableSeat::where('id', $response['handTable']->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        $player2 = PlayerAction::where('id', $response['actions']->slice(1, 1)->first()->id)->first();
        $player2->action_id = Action::where('name', 'Fold')->first()->id;
        $player2->bet_amount = null;
        $player2->active = 0;
        $player2->updated_at = date('Y-m-d H:i:s', strtotime('- 8 seconds'));
        $player2->save();

        TableSeat::where('id', $response['handTable']->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Check')->first()->id,
                'bet_amount' => null,
                'active' => 1
            ]);

        $response = $this->gamePlay->play();

        $this->assertCount(2, $response['hand']->streets->fresh());

    }

    /**
     * @test
     * @return void
     */
    public function if_the_dealer_is_seat_two_and_the_first_active_seat_on_a_new_street_the_first_active_seat_after_them_will_be_first_to_act()
    {
        $response = $this->gamePlay->start(1);

        $player1 = PlayerAction::where('id', $response['actions']->slice(0, 1)->first()->id)->first()->fresh();
        $player1->action_id = Action::where('name', 'Call')->first()->id;
        $player1->bet_amount = 50.0;
        $player1->active = 1;
        $player1->updated_at = date('Y-m-d H:i:s', strtotime('- 8 seconds'));
        $player1->save();

        TableSeat::where('id', $response['handTable']->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        $player2 = PlayerAction::where('id', $response['actions']->slice(1, 1)->first()->id)->first()->fresh();
        $player2->action_id = Action::where('name', 'Call')->first()->id;
        $player2->bet_amount = 50.0;
        $player2->active = 1;
        $player2->updated_at = date('Y-m-d H:i:s', strtotime('- 8 seconds'));
        $player2->save();

        TableSeat::where('id', $response['handTable']->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        $player3 = PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)->first()->fresh();
        $player3->action_id = Action::where('name', 'Fold')->first()->id;
        $player3->bet_amount = null;
        $player3->active = 0;
        $player3->updated_at = date('Y-m-d H:i:s', strtotime('- 8 seconds'));
        $player3->save();

        TableSeat::where('id', $response['handTable']->tableSeats->slice(2, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        PlayerAction::where('id', $response['actions']->slice(3, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Check')->first()->id,
                'bet_amount' => null,
                'active' => 1
            ]);

        $response = $this->gamePlay->play();

        $this->assertTrue($response['players'][3]['action_on']);
        $this->assertCount(2, $response['hand']->streets->fresh());

    }

    /**
     * @test
     * @return void
     */
    public function with_three_players_if_the_dealer_is_the_first_active_seat_on_a_new_street_the_first_active_seat_after_them_will_be_first_to_act()
    {

        TableSeat::where([
            'table_id' => $this->gamePlay->handTable->id,
            'player_id' => $this->player4->id
        ])->delete();

        $this->player4 = null;

        $response = $this->gamePlay->start();

        // Player 1 Raises BB
        $player1 = PlayerAction::where('id', $response['actions']->slice(0, 1)->first()->id)->first();

        $player1->action_id = Action::where('name', 'Raise')->first()->id;
        $player1->bet_amount = 100.0;
        $player1->active = 1;
        $player1->updated_at = date('Y-m-d H:i:s', strtotime('- 2 seconds'));
        $player1->save();

        TableSeat::where('id', $response['handTable']->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 2 Folds
        $player2 = PlayerAction::where('id', $response['actions']->slice(1, 1)->first()->id)->first();

        $player2->action_id = Action::where('name', 'Fold')->first()->id;
        $player2->bet_amount = null;
        $player2->active = 0;
        $player2->updated_at = date('Y-m-d H:i:s', strtotime('- 2 seconds'));
        $player2->save();

        TableSeat::where('id', $response['handTable']->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        // Player 3 Calls
        PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1
            ]);

        $response = $this->gamePlay->play();

        $this->assertCount(2, $response['hand']->streets->fresh());
        $this->assertTrue($response['players'][2]['action_on']);

    }

    /**
     * @test
     * @return void
     */
    public function it_adds_a_player_that_calls_the_big_blind_to_the_list_of_table_seats_that_can_continue()
    {
        $response = $this->gamePlay->start();

        $this->assertCount(0, $response['handTable']->tableSeats->where('can_continue', 1));

        // Player 4 Calls BB
        PlayerAction::where('id', $response['actions']->slice(3, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1
            ]);

        $response = $this->gamePlay->play();

        $this->assertCount(1, $response['handTable']->tableSeats->where('can_continue', 1));
        $this->assertEquals(1, $response['handTable']->tableSeats->fresh()->slice(3, 1)->first()->can_continue);

    }

    /**
     * @test
     * @return void
     */
    public function it_removes_a_folded_player_from_the_list_of_seats_that_can_continue()
    {
        $response = $this->gamePlay->start();

        $this->assertCount(0, $response['handTable']->tableSeats->where('can_continue', 1));

        // Player 4 Calls BB
        $player4 = PlayerAction::where('id', $response['actions']->slice(3, 1)->first()->id)->first();
        $player4->action_id = Action::where('name', 'Call')->first()->id;
        $player4->bet_amount = 50.0;
        $player4->active = 1;
        $player4->updated_at = date('Y-m-d H:i:s', strtotime('- 2 seconds'));
        $player4->save();

        TableSeat::query()->where('id', $response['handTable']->tableSeats->slice(3, 1)->first()->id)
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

        $response = $this->gamePlay->play();

        $this->assertCount(1, $response['handTable']->tableSeats->where('can_continue', 1));
        $this->assertEquals(0, $response['handTable']->tableSeats->slice(0, 1)->first()->can_continue);

    }

    /**
     * @test
     * @return void
     */
    public function the_pre_flop_action_will_initially_be_on_the_player_after_big_blind()
    {
        $response = $this->gamePlay->start();

        $this->assertTrue($response['players'][3]['action_on']);

        $this->assertTrue($response['players'][3]['availableOptions']->contains('name', 'Fold'));
        $this->assertTrue($response['players'][3]['availableOptions']->contains('name', 'Call'));
        $this->assertTrue($response['players'][3]['availableOptions']->contains('name', 'Raise'));

    }

    /**
     * @test
     * @return void
     */
    public function with_three_players_the_pre_flop_action_will_initially_be_on_player_one()
    {

        TableSeat::where([
            'table_id' => $this->gamePlay->handTable->id,
            'player_id' => $this->player4->id
        ])->delete();

        $this->player4 = null;

        $response = $this->gamePlay->start();

        $this->assertTrue($response['players'][0]['action_on']);

        $this->assertTrue($response['players'][0]['availableOptions']->contains('name', 'Fold'));
        $this->assertTrue($response['players'][0]['availableOptions']->contains('name', 'Call'));
        $this->assertTrue($response['players'][0]['availableOptions']->contains('name', 'Raise'));

    }

    /**
     * @test
     * @return void
     */
    public function the_pre_flop_action_will_be_back_on_the_big_blind_caller_if_the_big_blind_raises()
    {
        $response = $this->gamePlay->start();

        $this->assertCount(1, $response['hand']->streets->fresh());

        $player4 = PlayerAction::where('id', $response['actions']->slice(3, 1)->first()->id)->first();
        $player4->action_id = Action::where('name', 'Call')->first()->id;
        $player4->bet_amount = 50.0;
        $player4->active = 1;
        $player4->updated_at = date('Y-m-d H:i:s', strtotime('- 2 seconds'));
        $player4->save();

        TableSeat::query()->where('id', $response['handTable']->tableSeats->slice(3, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        $player1 = PlayerAction::where('id', $response['actions']->slice(0, 1)->first()->id)->first();
        $player1->action_id = Action::where('name', 'Call')->first()->id;
        $player1->bet_amount = null;
        $player1->active = 0;
        $player1->updated_at = date('Y-m-d H:i:s', strtotime('- 2 seconds'));
        $player1->save();

        TableSeat::query()->where('id', $response['handTable']->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        // Player 2 Folds
        $player2 = PlayerAction::where('id', $response['actions']->slice(1, 1)->first()->id)->first();
        $player2->action_id = Action::where('name', 'Fold')->first()->id;
        $player2->bet_amount = null;
        $player2->active = 0;
        $player2->updated_at = date('Y-m-d H:i:s', strtotime('- 2 seconds'));
        $player2->save();

        TableSeat::query()->where('id', $response['handTable']->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        // BB Raises
        PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Raise')->first()->id,
                'bet_amount' => 100.0,
                'active' => 1,
                'updated_at' => date('Y-m-d H:i:s', strtotime('+ 1 second')) // Realistically this needs to be manually set for testing to work
            ]);

        $response = $this->gamePlay->play();

        $this->assertCount(1, $response['hand']->streets->fresh());
        $this->assertTrue($response['players'][3]['action_on']);

    }

}

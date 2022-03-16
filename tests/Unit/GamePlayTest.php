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
    public function it_can_start_the_game()
    {
        $response = $this->gamePlay->start();

        // There are 3 players at the table
        $this->assertCount(3, $response['actions']);

        // The small blind was posted
        $this->assertEquals(25.0, $response['actions']->slice(0, 1)->first()->bet_amount);
        $this->assertEquals('Bet', $response['actions']->slice(0, 1)->first()->action->name);

        // The big blind was posted
        $this->assertEquals(50.0, $response['actions']->slice(1, 1)->first()->bet_amount);
        $this->assertEquals('Bet', $response['actions']->slice(1, 1)->first()->action->name);

        // The last player at the table has not acted yet
        $this->assertEquals(null, $response['actions']->slice(2, 1)->first()->bet_amount);
        $this->assertEquals(null, $response['actions']->slice(2, 1)->first()->action_id);

        // Each player in the hand has 2 whole cards
        foreach($response['handTable']->players as $player){
            $this->assertCount(2, $player->wholeCards->where('hand_id', $response['hand']->id));
        }

    }

    /**
     * @test
     * @return void
     */
    public function it_can_deal_a_new_street()
    {
        $response = $this->gamePlay->start();

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

        // Player 2 Checks
        PlayerAction::where('id', $response['actions']->slice(1, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Check')->first()->id,
                'bet_amount' => null,
                'active' => 1
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        $response = $this->gamePlay->play();

        $this->assertCount(2, $response['hand']->streets->fresh());

    }

    /**
     * @test
     * @return void
     */
    public function it_adds_a_player_that_calls_the_big_blind_to_the_list_of_table_seats_that_can_continue()
    {
        $response = $this->gamePlay->start();

        $this->assertCount(0, $response['handTable']->tableSeats->where('can_continue', 1));

        // Player 3 Calls BB
        PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1
            ]);

        $response = $this->gamePlay->play();

        $this->assertCount(1, $response['handTable']->tableSeats->where('can_continue', 1));
        $this->assertEquals(1, $response['handTable']->tableSeats->fresh()->slice(2, 1)->first()->can_continue);

    }

    /**
     * @test
     * @return void
     */
    public function it_removes_a_folded_player_from_the_list_of_seats_that_can_continue()
    {
        $response = $this->gamePlay->start();

        $this->assertCount(0, $response['handTable']->tableSeats->where('can_continue', 1));

        // Player 3 Calls BB
        PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1,
            ]);

        TableSeat::query()->where('id', $response['handTable']->tableSeats->slice(2, 1)->first()->id)
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

}

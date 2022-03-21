<?php

namespace Tests\Unit;

use App\Classes\GamePlay;
use App\Models\Action;
use App\Models\Hand;
use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\TableSeat;

class ActionOptionsTest extends TestEnvironment
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
    public function a_player_facing_a_bet_can_fold_call_or_raise()
    {
        $gamePlay = $this->gamePlay->start();

        $this->assertTrue($gamePlay['players'][0]['action_on']);

        $this->assertTrue($gamePlay['players'][0]['availableOptions']->contains('name', 'Fold'));
        $this->assertTrue($gamePlay['players'][0]['availableOptions']->contains('name', 'Call'));
        $this->assertTrue($gamePlay['players'][0]['availableOptions']->contains('name', 'Raise'));


    }

    /**
     * @test
     * @return void
     */
    public function a_player_facing_a_raise_can_fold_call_or_raise()
    {
        $this->gamePlay->start();

        // Player 1 Raises BB
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Raise')->first()->id,
                'bet_amount' => 100.0,
                'active' => 1,
            ]);

        TableSeat::query()->where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 1 Folds
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(1, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Fold')->first()->id,
                'bet_amount' => null,
                'active' => 0
            ]);

        $gamePlay = $this->gamePlay->play();

        // Action On BB
        $this->assertTrue($gamePlay['players'][2]['action_on']);

        $this->assertTrue($gamePlay['players'][2]['availableOptions']->contains('name', 'Fold'));
        $this->assertTrue($gamePlay['players'][2]['availableOptions']->contains('name', 'Call'));
        $this->assertTrue($gamePlay['players'][2]['availableOptions']->contains('name', 'Raise'));

    }

    /**
     * @test
     * @return void
     */
    public function a_folded_player_has_no_options()
    {
        $this->gamePlay->start();

        // Player 1 Raises BB
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Raise')->first()->id,
                'bet_amount' => 100.0,
                'active' => 1,
            ]);

        TableSeat::query()->where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 1 Folds
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(1, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Fold')->first()->id,
                'bet_amount' => null,
                'active' => 0
            ]);

        $gamePlay = $this->gamePlay->play();

        // Action On BB
        $this->assertTrue($gamePlay['players'][2]['action_on']);

        $this->assertEmpty($gamePlay['players'][1]['availableOptions']);

    }

    /**
     * @test
     * @return void
     */
    public function the_big_blind_facing_a_call_can_fold_check_or_raise()
    {
        $this->gamePlay->start();

        // Player 1 Calls BB
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1,
            ]);

        TableSeat::query()->where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 2 Folds
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(1, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Fold')->first()->id,
                'bet_amount' => null,
                'active' => 0
            ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertTrue($gamePlay['players'][2]['action_on']);

        $this->assertTrue($gamePlay['players'][2]['availableOptions']->contains('name', 'Fold'));
        $this->assertTrue($gamePlay['players'][2]['availableOptions']->contains('name', 'Check'));
        $this->assertTrue($gamePlay['players'][2]['availableOptions']->contains('name', 'Raise'));

    }

    /**
     * @test
     * @return void
     */
    public function a_player_facing_a_call_can_fold_call_or_raise()
    {
        $this->gamePlay->start();

        // Player 1 Calls BB
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1,
            ]);

        $gamePlay = $this->gamePlay->play();

        // Action on SB
        $this->assertTrue($gamePlay['players'][1]['action_on']);

        $this->assertTrue($gamePlay['players'][1]['availableOptions']->contains('name', 'Fold'));
        $this->assertTrue($gamePlay['players'][1]['availableOptions']->contains('name', 'Call'));
        $this->assertTrue($gamePlay['players'][1]['availableOptions']->contains('name', 'Raise'));

    }

}

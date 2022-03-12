<?php

namespace Tests\Unit;

use App\Classes\GamePlay;
use App\Models\Hand;
use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\Table;
use App\Models\TableSeat;

class GamePlayFlopTest extends TestEnvironment
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->table = Table::factory([
            'name' => 'Table 1',
            'seats' => 3
        ])->create();

        $this->player1 = Player::factory()->create();
        $this->player2 = Player::factory()->create();
        $this->player3 = Player::factory()->create();

        TableSeat::factory([
            'table_id' => $this->table->id,
            'player_id' => $this->player1->id
        ])->create();

        TableSeat::factory([
            'table_id' => $this->table->id,
            'player_id' => $this->player2->id
        ])->create();

        TableSeat::factory([
            'table_id' => $this->table->id,
            'player_id' => $this->player3->id
        ])->create();

        $this->gamePlay = new GamePlay(Hand::create());
    }

    /**
     * @test
     * @return void
     */
    public function it_can_deal_3_cards_to_a_flop()
    {
        $response = $this->gamePlay->start();

        // Player 3 Calls BB
        PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => 3,
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
                'action_id' => 1,
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
                'action_id' => 2,
                'bet_amount' => null,
                'active' => 1
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        $response = $this->gamePlay->play();

        $this->assertCount(2, $response['hand']->streets);
        $this->assertCount(3, $response['hand']->streets->slice(1, 1)->first()->cards);

    }

}

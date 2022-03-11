<?php

namespace Tests\Unit;

use App\Classes\GamePlay;
use App\Models\Hand;
use App\Models\Player;
use App\Models\Table;
use App\Models\TableSeat;

class GamePlayTest extends TestEnvironment
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
    public function it_can_start_the_game()
    {
        $this->gamePlay->start();
    }

}

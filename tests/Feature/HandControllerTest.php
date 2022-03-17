<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\Table;
use App\Models\TableSeat;
use Tests\Unit\TestEnvironment;

class HandControllerTest extends TestEnvironment
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->player1 = Player::factory()->create();
        $this->player2 = Player::factory()->create();
        $this->player3 = Player::factory()->create();

        TableSeat::factory([
            'table_id' => Table::where('name', 'Table 1')->first()->id,
            'player_id' => $this->player1->id
        ])->create();

        TableSeat::factory([
            'table_id' => Table::where('name', 'Table 1')->first()->id,
            'player_id' => $this->player2->id
        ])->create();

        TableSeat::factory([
            'table_id' => Table::where('name', 'Table 1')->first()->id,
            'player_id' => $this->player3->id
        ])->create();

    }
    /**
     * @test
     * @return void
     */
    public function a_new_hand_can_be_started_and_index_view_returned()
    {
        $response = $this->get('hand');

        $response->assertStatus(200);
        $response->assertViewIs('index');
    }

}

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
    public function a_new_hand_can_be_started()
    {
        $response = $this->get('hand');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function the_action_will_be_on_the_player_after_the_big_blind_once_a_hand_is_started()
    {
        $response = $this->get('hand');

        $this->assertEquals($this->player3->id, $response['actionOn']['player_id']);
    }
}

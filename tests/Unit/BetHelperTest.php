<?php

namespace Tests\Unit;

use App\Helpers\BetHelper;
use App\Helpers\PotHelper;
use App\Models\Hand;
use App\Models\Player;
use App\Models\Pot;
use App\Models\Stack;
use App\Models\Table;
use App\Models\TableSeat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BetHelperTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function a_bet_amount_is_added_to_the_pot_and_subtracted_from_the_player_stack()
    {

        $table = Table::factory()->create([
            'name' => 'Table 1',
            'seats' => 2
        ]);

        $player = Player::factory()->create();

        TableSeat::factory([
            'table_id' => $table->id,
            'player_id' => $player->id
        ])->create();

        $stack = Stack::factory()->create([
            'amount' => 1000,
            'table_id' => $table->id,
            'player_id' => $player->id
        ]);

        $hand = Hand::factory()->create([
            'table_id' => $table->id
        ]);

        $pot = Pot::factory()->create([
            'amount' => 0,
            'hand_id' => $hand->id
        ]);

        $this->assertEquals(1000, $player->fresh()->stacks->where('id', $stack->id)->first()->amount);

        BetHelper::handle($hand->fresh(), $player->fresh(), 150);

        $this->assertEquals(150, $pot->fresh()->amount);
        $this->assertEquals(850, $player->fresh()->stacks->where('id', $stack->id)->first()->amount);




    }
}

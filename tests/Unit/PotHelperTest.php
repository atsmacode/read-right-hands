<?php

namespace Tests\Unit;

use App\Helpers\PotHelper;
use App\Models\Hand;
use App\Models\Player;
use App\Models\Pot;
use App\Models\Stack;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PotHelperTest extends TestEnvironment
{

    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function a_pot_can_be_awarded_to_a_player()
    {

        $table = Table::first();
        $player = Player::factory()->create();
        $stack = Stack::factory()->create([
            'amount' => 1000,
            'table_id' => $table->id,
            'player_id' => $player->id
        ]);

        $hand = Hand::factory()->create([
            'table_id' => $table->id
        ]);
        $pot = Pot::factory()->create([
            'amount' => 75,
            'hand_id' => $hand->id
        ]);

        $this->assertEquals(1000, $player->stacks->where('id', $stack->id)->first()->amount);

        PotHelper::awardPot($pot->fresh(), $player);

        $this->assertEquals(1075, $player->fresh()->stacks->where('id', $stack->id)->first()->amount);



    }
}

<?php

namespace Tests\Unit;

use App\Models\Player;
use App\Models\Stack;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StackTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function a_player_can_have_a_stack()
    {

        $table = Table::factory()->create([
            'name' => 'Table 1',
            'seats' => 2
        ]);

        $player = Player::factory()->create();

        $this->assertEmpty($player->stacks);

        $stack = Stack::factory()->create([
            'amount' => 1000,
            'table_id' => $table->id,
            'player_id' => $player->id
        ]);

        $player->stacks()->save($stack);

        $this->assertContains($stack->id, $player->fresh()->stacks->pluck('id'));


    }
}

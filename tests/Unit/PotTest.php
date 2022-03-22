<?php

namespace Tests\Unit;

use App\Models\Hand;
use App\Models\Pot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PotTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function a_hand_can_have_a_pot()
    {

        $hand = Hand::factory()->create();

        $this->assertNull($hand->pot);

        $pot = Pot::factory()->create([
            'amount' => 75,
            'hand_id' => $hand->id
        ]);

        $hand->pot()->save($pot);

        $this->assertEquals($pot->id, $hand->fresh()->pot->id);


    }
}

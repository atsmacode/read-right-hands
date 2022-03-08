<?php

namespace Tests\Unit;

use App\Models\Rank;
use App\Models\Suit;

class CardTest extends TestEnvironment
{

    /**
     * @test
     * @return void
     */
    public function a_card_has_a_suite()
    {
        $this->assertInstanceOf(Suit::class, $this->deck->first()->suit);
    }

    /**
     * @test
     * @return void
     */
    public function a_card_has_a_rank()
    {
        $this->assertInstanceOf(Rank::class, $this->deck->first()->rank);
    }

}

<?php

namespace Tests\Unit;

class DeckTest extends TestEnvironment
{

    /**
     * @test
     * @return void
     */
    public function a_deck_has_52_cards()
    {
        $this->assertCount(52, $this->deck);
    }

}

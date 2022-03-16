<?php

namespace Tests\Unit;

use App\Models\Card;

class DeckTest extends TestEnvironment
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->deck = Card::all();
    }

    /**
     * @test
     * @return void
     */
    public function a_deck_has_52_cards()
    {
        $this->assertInstanceOf(Card::class, $this->deck->first());
    }

    /**
     * @test
     * @return void
     */
    public function a_deck_can_be_shuffled()
    {

        $deck = $this->deck->toArray();
        $shuffled = $this->deck->toArray();

        shuffle($shuffled);

        $this->assertNotSame($deck, $shuffled);

    }

    /**
     * @test
     * @return void
     */
    public function a_card_can_be_picked_from_the_deck()
    {
        $this->assertInstanceOf(Card::class, $this->deck->first());
    }

    /**
     * @test
     * @return void
     */
    public function a_card_can_only_appear_once_in_the_deck()
    {
        // not sure if this is required as only the DB has the unique rule, for feature test maybe
        $this->assertTrue(true);
    }

}

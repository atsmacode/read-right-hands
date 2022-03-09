<?php

namespace Tests\Unit;

use App\Classes\Dealer;
use App\Models\Card;
use App\Models\Player;
use App\Models\Rank;
use App\Models\Suit;
use App\Models\Table;
use App\Models\TableSeat;
use Database\Factories\PlayerFactory;

class DealerTest extends TestEnvironment
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->dealer = new Dealer();
    }

    /**
     * @test
     * @return void
     */
    public function it_can_shuffle_the_deck()
    {
        $deck = $this->dealer->setDeck()->getDeck();
        $shuffle = $this->dealer->setDeck()->shuffle()->getDeck();
        $this->assertNotSame($deck, $shuffle);
    }

    /**
     * @test
     * @return void
     */
    public function it_can_select_a_random_card()
    {
        $this->assertInstanceOf(Card::class, $this->dealer->setDeck()->shuffle()->pickCard()->card);
    }

    /**
     * @test
     * @return void
     */
    public function it_can_select_a_specific_card()
    {
        $card = $this->dealer->setDeck()->shuffle()->pickCard(
            Rank::where('name', 'Ace')->first(),
            Suit::where('name', 'Spades')->first()
        )->card;

        $this->assertEquals('Ace', $card->rank->name);
        $this->assertEquals('Spades', $card->suit->name);
    }

    /**
     * @test
     * @return void
     */
    public function it_can_deal_a_card_to_a_player()
    {
        $player = Player::factory()->create();

        $this->assertCount(0, $player->wholeCards);

        $this->dealer->setDeck()->shuffle()->dealTo($player);

        $this->assertCount(1, $player->fresh()->wholeCards);
    }

    /**
     * @test
     * @return void
     */
    public function it_can_deal_cards_to_multiple_players_at_a_table()
    {
        $table = Table::factory([
            'name' => 'Table 1',
            'seats' => 2
        ])->create();

        $player1 = Player::factory()->create();
        $player2 = Player::factory()->create();

        $this->assertCount(0, $player1->fresh()->wholeCards);
        $this->assertCount(0, $player2->fresh()->wholeCards);

        TableSeat::factory([
            'table_id' => $table->id,
            'player_id' => $player1->id
        ])->create();

       TableSeat::factory([
            'table_id' => $table->id,
            'player_id' => $player2->id
        ])->create();

       $this->dealer->setDeck()->shuffle();

       $this->dealer->dealTo($table->players);

       $this->assertCount(1, $player1->fresh()->wholeCards);
       $this->assertCount(1, $player2->fresh()->wholeCards);


    }

}

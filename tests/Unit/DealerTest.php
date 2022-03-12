<?php

namespace Tests\Unit;

use App\Classes\Dealer;
use App\Models\Card;
use App\Models\Player;
use App\Models\Rank;
use App\Models\Suit;
use App\Models\Table;
use App\Models\TableSeat;

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
        $this->assertInstanceOf(Card::class, $this->dealer->setDeck()->shuffle()->pickCard()->getCard());
    }

    /**
     * @test
     * @return void
     */
    public function it_can_select_a_specific_card()
    {
        $card = $this->dealer->setDeck()->pickCard(
            Rank::where('name', 'Ace')->first(),
            Suit::where('name', 'Spades')->first()
        )->getCard();

        $this->assertEquals('Ace', $card->rank->name);
        $this->assertEquals('Spades', $card->suit->name);
    }

    /**
     * @test
     * @return void
     */
    public function once_a_card_is_dealt_it_is_no_longer_in_the_deck()
    {

        $rank = Rank::where('name', 'Ace')->first();
        $suit = Suit::where('name', 'Spades')->first();

        $player = Player::factory()->create();

        $this->dealer->setDeck()->pickCard($rank, $suit)->dealCardTo($player);

        $this->assertNotContains($this->dealer->getCard(), $this->dealer->getDeck()->fresh());
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

       $this->dealer->setDeck()->shuffle()->dealTo($table->fresh()->players);

       $this->assertCount(1, $player1->fresh()->wholeCards);
       $this->assertCount(1, $player2->fresh()->wholeCards);


    }

}

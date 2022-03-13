<?php

namespace Tests\Unit;

use App\Classes\GamePlay;
use App\Classes\HandIdentifier;
use App\Models\Action;
use App\Models\Card;
use App\Models\Hand;
use App\Models\HandStreet;
use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\Rank;
use App\Models\Street;
use App\Models\Suit;
use App\Models\Table;
use App\Models\TableSeat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;

class HandIdentifierTest extends TestEnvironment
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->handIdentifier = new HandIdentifier();
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_card_with_the_highest_rank()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Queen')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Jack')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id
            ])->first(),
        ];

        $this->assertEquals(
            Rank::where('name', 'King')->first()->ranking,
            $this->handIdentifier->identify($wholeCards, $communityCards)->highestCard()
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_an_ace_as_the_card_with_the_highest_rank()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Queen')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Jack')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id
            ])->first(),
        ];

        $this->assertEquals(
            Rank::where('name', 'Ace')->first()->ranking,
            $this->handIdentifier->identify($wholeCards, $communityCards)->highestCard()
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_a_pair()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Jack')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id
            ])->first(),
        ];

        $this->assertTrue($this->handIdentifier->identify($wholeCards, $communityCards)->hasPair());
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_two_pair()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id
            ])->first(),
        ];

        $this->assertTrue($this->handIdentifier->identify($wholeCards, $communityCards)->hasTwoPair());
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_three_of_a_kind()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id
            ])->first(),
        ];

        $this->assertEquals(
            Rank::where('name', 'King')->first(),
            $this->handIdentifier->identify($wholeCards, $communityCards)->hasThreeOfAKind()
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_a_flush()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id
            ])->first(),
        ];

        $this->assertEquals(
            Suit::where('name', 'Spades')->first(),
            $this->handIdentifier->identify($wholeCards, $communityCards)->hasFlush()
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_a_full_house()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id
            ])->first(),
        ];

        $this->assertTrue($this->handIdentifier->identify($wholeCards, $communityCards)->hasFullHouse());
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_four_of_a_kind()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id
            ])->first(),
        ];

        $this->assertEquals(
            Rank::where('name', 'King')->first(),
            $this->handIdentifier->identify($wholeCards, $communityCards)->hasFourOfAKind()
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_a_royal_flush()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Queen')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Jack')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id
            ])->first(),
        ];

        $this->assertTrue($this->handIdentifier->identify($wholeCards, $communityCards)->hasRoyalFlush());
    }

}

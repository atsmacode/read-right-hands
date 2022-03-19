<?php

namespace Tests\Unit;

use App\Classes\GamePlay;
use App\Classes\HandIdentifier;
use App\Models\Action;
use App\Models\Card;
use App\Models\Hand;
use App\Models\HandStreet;
use App\Models\HandType;
use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\Rank;
use App\Models\Street;
use App\Models\Suit;
use App\Models\Table;
use App\Models\TableSeat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;

class HandIdentifierKickerTest extends TestEnvironment
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
    public function it_can_identify_the_kicker_and_active_ranks_for_a_high_card_hand()
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
                'suit_id' => Suit::where('name', 'Hearts')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Three')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Four')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals(
            14,
            $this->handIdentifier->highCard
        );

        $this->assertEquals(
            Rank::where('name', 'King')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );

        $this->assertContains(
            14,
            $this->handIdentifier->identifiedHandType['activeCards']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_and_active_ranks_for_a_pair()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Queen')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Jack')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Four')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals(
            Rank::where('name', 'King')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );

        $this->assertContains(
            Rank::where('name', 'Nine')->first()->ranking,
            $this->handIdentifier->identifiedHandType['activeCards']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_a_high_ace_kicker_for_a_pair()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Queen')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Jack')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Four')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals(
            14,
            $this->handIdentifier->identifiedHandType['kicker']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_a_high_ace_kicker_for_two_pair()
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
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Queen')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals(
            14,
            $this->handIdentifier->identifiedHandType['kicker']
        );

        $this->assertContains(
            Rank::where('name', 'Queen')->first()->ranking,
            $this->handIdentifier->identifiedHandType['activeCards']
        );

        $this->assertContains(
            Rank::where('name', 'King')->first()->ranking,
            $this->handIdentifier->identifiedHandType['activeCards']
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_and_active_ranks_for_two_pair()
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

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals(
            Rank::where('name', 'Ten')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );

        $this->assertContains(
            Rank::where('name', 'Ace')->first()->ranking,
            $this->handIdentifier->identifiedHandType['activeCards']
        );

        $this->assertContains(
            Rank::where('name', 'King')->first()->ranking,
            $this->handIdentifier->identifiedHandType['activeCards']
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_a_high_ace_kicker_for_three_of_a_kind()
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

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals(
            14,
            $this->handIdentifier->identifiedHandType['kicker']
        );

        $this->assertContains(
            Rank::where('name', 'King')->first()->ranking,
            $this->handIdentifier->identifiedHandType['activeCards']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_and_active_ranks_for_three_of_a_kind()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Queen')->first()->id,
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

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals(
            Rank::where('name', 'Queen')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );

        $this->assertContains(
            Rank::where('name', 'King')->first()->ranking,
            $this->handIdentifier->identifiedHandType['activeCards']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_for_a_straight_where_the_highest_ranked_card_is_not_in_the_straight()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);
        $this->assertEquals('Straight', $this->handIdentifier->identifiedHandType['handType']->name);

        $this->assertEquals(
            Rank::where('name', 'Ten')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_for_a_straight_where_the_two_highest_ranked_connecting_cards_are_not_in_the_straight()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id,
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals('Straight', $this->handIdentifier->identifiedHandType['handType']->name);

        $this->assertEquals(
            Rank::where('name', 'Ten')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_for_a_straight_where_there_are_more_than_five_connecting_cards()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Five')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);
        $this->assertEquals('Straight', $this->handIdentifier->identifiedHandType['handType']->name);

        $this->assertEquals(
            Rank::where('name', 'Ten')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_for_an_ace_to_five_straight_where_there_are_also_two_deuces()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Three')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Four')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Five')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals('Straight', $this->handIdentifier->identifiedHandType['handType']->name);

        $this->assertEquals(
            Rank::where('name', 'Five')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_for_an_ace_to_five_straight()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Queen')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Three')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Four')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Five')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);
        $this->assertEquals('Straight', $this->handIdentifier->identifiedHandType['handType']->name);

        $this->assertEquals(
            Rank::where('name', 'Five')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_for_an_ace_high_straight()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id
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
                'rank_id' => Rank::where('name', 'Five')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);
        $this->assertEquals('Straight', $this->handIdentifier->identifiedHandType['handType']->name);

        $this->assertEquals(
            14,
            $this->handIdentifier->identifiedHandType['kicker']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_for_a_straight_with_a_duplicate_rank()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Four')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Five')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);
        $this->assertEquals('Straight', $this->handIdentifier->identifiedHandType['handType']->name);

        $this->assertEquals(
            Rank::where('name', 'Eight')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_an_ace_high_flush()
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

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals('Flush', $this->handIdentifier->identifiedHandType['handType']->name);

        $this->assertEquals(
            14,
            $this->handIdentifier->identifiedHandType['kicker']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_an_ace_high_kicker_for_four_of_a_kind()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
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
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);

        $this->assertEquals('Four of a Kind', $this->handIdentifier->identifiedHandType['handType']->name);

        $this->assertEquals(
            14,
            $this->handIdentifier->identifiedHandType['kicker']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_the_kicker_for_a_straight_flush()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Four')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Five')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);
        $this->assertEquals('Straight Flush', $this->handIdentifier->identifiedHandType['handType']->name);

        $this->assertEquals(
            Rank::where('name', 'Eight')->first()->ranking,
            $this->handIdentifier->identifiedHandType['kicker']
        );

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_a_straight_flush_with_a_duplicate_rank()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Four')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Five')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Clubs')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);
        $this->assertEquals('Straight Flush', $this->handIdentifier->identifiedHandType['handType']->name);

    }

    /**
     * @test
     * @return void
     */
    public function it_can_identify_two_suited_connecting_cards_and_three_separate_suited_connecting_cards_is_not_a_straight_flush()
    {
        $wholeCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Queen')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ];

        $communityCards = [
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Deuce')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id,
            ])->first(),
            Card::where([
                'rank_id' => Rank::where('name', 'Six')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id,
            ])->first(),
        ];

        $this->handIdentifier->identify($wholeCards, $communityCards);
        $this->assertFalse($this->handIdentifier->straightFlush);

    }

}

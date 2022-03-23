<?php

namespace Tests\Unit;

use App\Classes\GamePlay;
use App\Models\Action;
use App\Models\Card;
use App\Models\Hand;
use App\Models\HandStreet;
use App\Models\HandStreetCard;
use App\Models\HandType;
use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\Rank;
use App\Models\Street;
use App\Models\Suit;
use App\Models\Table;
use App\Models\TableSeat;
use App\Models\WholeCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;

class ShowdownKickerAndRankingTest extends TestEnvironment
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->gamePlay = new GamePlay(Hand::create());

        $this->player1 = Player::factory()->create();
        $this->player2 = Player::factory()->create();
        $this->player3 = Player::factory()->create();

        TableSeat::factory([
            'table_id' => $this->gamePlay->handTable->id,
            'player_id' => $this->player1->id
        ])->create();

        TableSeat::factory([
            'table_id' => $this->gamePlay->handTable->id,
            'player_id' => $this->player2->id
        ])->create();

        TableSeat::factory([
            'table_id' => $this->gamePlay->handTable->id,
            'player_id' => $this->player3->id
        ])->create();

        $this->handTypes = HandType::all();
    }

    /**
     * @test
     * @return void
     */
    public function high_card_king_beats_high_card_queen()
    {

        $this->gamePlay->initiateStreetActions()
            ->initiatePlayerStacks()
            ->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'King',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Three',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Queen',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Seven',
                'suit' => 'Diamonds'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Four',
                'suit' => 'Clubs'
            ],
            [
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Deuce',
                'suit' => 'Clubs'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Nine',
            'suit' => 'Diamonds'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Ten',
            'suit' => 'Spades'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $this->gamePlay->hand->playerActions->fresh(),
            'handTable' => $this->gamePlay->handTable->fresh()
        ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $gamePlay['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'High Card')->first()->id, $gamePlay['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function a_pair_of_jacks_beats_a_pair_of_tens()
    {

        $this->gamePlay->initiateStreetActions()
            ->initiatePlayerStacks()
            ->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Three',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Ten',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Seven',
                'suit' => 'Diamonds'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Four',
                'suit' => 'Clubs'
            ],
            [
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Deuce',
                'suit' => 'Clubs'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Nine',
            'suit' => 'Diamonds'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Ten',
            'suit' => 'Spades'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $this->gamePlay->hand->playerActions->fresh(),
            'handTable' => $this->gamePlay->handTable->fresh()
        ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $gamePlay['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Pair')->first()->id, $gamePlay['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function kings_and_tens_beats_jacks_and_tens()
    {

        $this->gamePlay->initiateStreetActions()
            ->initiatePlayerStacks()
            ->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player1,
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Ten',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Ten',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'King',
                'suit' => 'Diamonds'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'King',
                'suit' => 'Clubs'
            ],
            [
                'rank' => 'Jack',
                'suit' => 'Hearts'
            ],
            [
                'rank' => 'Deuce',
                'suit' => 'Clubs'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Nine',
            'suit' => 'Diamonds'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Ten',
            'suit' => 'Hearts'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $this->gamePlay->hand->playerActions->fresh(),
            'handTable' => $this->gamePlay->handTable->fresh()
        ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $gamePlay['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Two Pair')->first()->id, $gamePlay['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function jacks_and_tens_with_king_kicker_beats_jacks_and_tens_with_lower_kicker()
    {

        $this->gamePlay->initiateStreetActions()
            ->initiatePlayerStacks()
            ->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player1,
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Nine',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player3,
                'rank' => 'King',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Jack',
                'suit' => 'Diamonds'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Three',
                'suit' => 'Clubs'
            ],
            [
                'rank' => 'Jack',
                'suit' => 'Hearts'
            ],
            [
                'rank' => 'Deuce',
                'suit' => 'Clubs'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Ten',
            'suit' => 'Clubs'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Ten',
            'suit' => 'Hearts'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $this->gamePlay->hand->playerActions->fresh(),
            'handTable' => $this->gamePlay->handTable->fresh()
        ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $gamePlay['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Two Pair')->first()->id, $gamePlay['winner']['handType']->id);
        $this->assertEquals(13, $gamePlay['winner']['kicker']);


    }

    /**
     * @test
     * @return void
     */
    public function three_jacks_beats_three_tens()
    {

        $this->gamePlay->initiateStreetActions()
            ->initiatePlayerStacks()
            ->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Jack',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Ten',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Ten',
                'suit' => 'Diamonds'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'King',
                'suit' => 'Clubs'
            ],
            [
                'rank' => 'Jack',
                'suit' => 'Hearts'
            ],
            [
                'rank' => 'Deuce',
                'suit' => 'Clubs'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Nine',
            'suit' => 'Diamonds'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Ten',
            'suit' => 'Hearts'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $this->gamePlay->hand->playerActions->fresh(),
            'handTable' => $this->gamePlay->handTable->fresh()
        ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $gamePlay['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Three of a Kind')->first()->id, $gamePlay['winner']['handType']->id);
        $this->assertEquals(11, $gamePlay['winner']['highestActiveCard']);

    }

    /**
     * @test
     * @return void
     */
    public function an_eight_high_straight_beats_a_seven_high_straight()
    {

        $this->gamePlay->initiateStreetActions()
            ->initiatePlayerStacks()
            ->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Eight',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Seven',
                'suit' => 'Hearts'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Seven',
                'suit' => 'Diamonds'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Five',
                'suit' => 'Clubs'
            ],
            [
                'rank' => 'Four',
                'suit' => 'Hearts'
            ],
            [
                'rank' => 'Three',
                'suit' => 'Hearts'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Six',
            'suit' => 'Diamonds'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'King',
            'suit' => 'Hearts'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $this->gamePlay->hand->playerActions->fresh(),
            'handTable' => $this->gamePlay->handTable->fresh()
        ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $gamePlay['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Straight')->first()->id, $gamePlay['winner']['handType']->id);
        $this->assertEquals(8, $gamePlay['winner']['kicker']);

    }

    /**
     * @test
     * @return void
     */
    public function an_ace_high_flush_beats_a_king_high_flush()
    {

        $this->gamePlay->initiateStreetActions()
            ->initiatePlayerStacks()
            ->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Ace',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Nine',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'King',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Seven',
                'suit' => 'Spades'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Five',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Four',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Jack',
                'suit' => 'Hearts'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Deuce',
            'suit' => 'Spades'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'King',
            'suit' => 'Hearts'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $this->gamePlay->hand->playerActions->fresh(),
            'handTable' => $this->gamePlay->handTable->fresh()
        ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $gamePlay['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Flush')->first()->id, $gamePlay['winner']['handType']->id);
        $this->assertEquals(14, $gamePlay['winner']['kicker']);

    }

    /**
     * @test
     * @return void
     */
    public function jacks_full_beats_tens_full()
    {

        $this->gamePlay->initiateStreetActions()
            ->initiatePlayerStacks()
            ->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Jack',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Ten',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Ten',
                'suit' => 'Diamonds'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'King',
                'suit' => 'Clubs'
            ],
            [
                'rank' => 'Jack',
                'suit' => 'Hearts'
            ],
            [
                'rank' => 'King',
                'suit' => 'Hearts'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Nine',
            'suit' => 'Diamonds'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Ten',
            'suit' => 'Hearts'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $this->gamePlay->hand->playerActions->fresh(),
            'handTable' => $this->gamePlay->handTable->fresh()
        ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $gamePlay['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Full House')->first()->id, $gamePlay['winner']['handType']->id);
        $this->assertEquals(11, $gamePlay['winner']['highestActiveCard']);

    }

    /**
     * @test
     * @return void
     */
    public function four_eights_beats_four_sixes()
    {

        $this->gamePlay->initiateStreetActions()
            ->initiatePlayerStacks()
            ->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Eight',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Eight',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Six',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Six',
                'suit' => 'Diamonds'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Eight',
                'suit' => 'Clubs'
            ],
            [
                'rank' => 'Eight',
                'suit' => 'Hearts'
            ],
            [
                'rank' => 'Six',
                'suit' => 'Hearts'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Six',
            'suit' => 'Clubs'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Ten',
            'suit' => 'Hearts'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $this->gamePlay->hand->playerActions->fresh(),
            'handTable' => $this->gamePlay->handTable->fresh()
        ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $gamePlay['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Four of a Kind')->first()->id, $gamePlay['winner']['handType']->id);
        $this->assertEquals(8, $gamePlay['winner']['highestActiveCard']);

    }

    /**
     * @test
     * @return void
     */
    public function an_eight_high_straight_flush_beats_a_seven_high_straight_flush()
    {

        $this->gamePlay->initiateStreetActions()
            ->initiatePlayerStacks()
            ->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Eight',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Seven',
                'suit' => 'Hearts'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Seven',
                'suit' => 'Diamonds'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Seven',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Six',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Five',
                'suit' => 'Spades'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Four',
            'suit' => 'Spades'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Three',
            'suit' => 'Spades'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $this->gamePlay->hand->playerActions->fresh(),
            'handTable' => $this->gamePlay->handTable->fresh()
        ]);

        $gamePlay = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $gamePlay['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Straight Flush')->first()->id, $gamePlay['winner']['handType']->id);
        $this->assertEquals(8, $gamePlay['winner']['kicker']);

    }

    protected function setWholeCards($wholeCards)
    {
        foreach($wholeCards as $wholeCard){
            WholeCard::factory([
                'player_id' => $wholeCard['player']->id,
                'card_id' => Card::where([
                    'rank_id' => Rank::where('name', $wholeCard['rank'])->first()->id,
                    'suit_id' => Suit::where('name', $wholeCard['suit'])->first()->id
                ])->first(),
                'hand_id' => $this->gamePlay->hand->id
            ])->create();
        }
    }

    protected function setflop($flopCards)
    {
        $flop = HandStreet::factory()->create([
            'street_id' => Street::where('name', $this->gamePlay->game->streets[1]['name'])->first()->id,
            'hand_id' => $this->gamePlay->hand->id
        ]);

        foreach($flopCards as $card){
            HandStreetCard::factory()->create([
                'hand_street_id' => $flop->id,
                'card_id' => Card::where([
                    'rank_id' => Rank::where('name', $card['rank'])->first()->id,
                    'suit_id' => Suit::where('name', $card['suit'])->first()->id
                ])->first()
            ]);
        }
    }

    protected function setTurn($turnCard)
    {
        $turn = HandStreet::factory()->create([
            'street_id' => Street::where('name', $this->gamePlay->game->streets[2]['name'])->first()->id,
            'hand_id' => $this->gamePlay->hand->id
        ]);

        HandStreetCard::factory()->create([
            'hand_street_id' => $turn->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', $turnCard['rank'])->first()->id,
                'suit_id' => Suit::where('name', $turnCard['suit'])->first()->id
            ])->first()
        ]);
    }

    protected function setRiver($riverCard)
    {
        $river = HandStreet::factory()->create([
            'street_id' => Street::where('name', $this->gamePlay->game->streets[3]['name'])->first()->id,
            'hand_id' => $this->gamePlay->hand->id
        ]);

        HandStreetCard::factory()->create([
            'hand_street_id' => $river->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', $riverCard['rank'])->first()->id,
                'suit_id' => Suit::where('name', $riverCard['suit'])->first()->id
            ])->first()
        ]);
    }

    protected function executeActions($gamePlay)
    {
        // Player 1 Calls BB
        PlayerAction::where('id', $gamePlay['actions']->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1
            ]);

        TableSeat::where('id', $gamePlay['handTable']->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 2 Folds
        PlayerAction::where('id', $gamePlay['actions']->slice(1, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Fold')->first()->id,
                'bet_amount' => 25.0,
                'active' => 0
            ]);

        TableSeat::where('id', $gamePlay['handTable']->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        // Player 3 Checks
        PlayerAction::where('id', $gamePlay['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Check')->first()->id,
                'bet_amount' => null,
                'active' => 1
            ]);

        TableSeat::where('id', $gamePlay['handTable']->tableSeats->slice(2, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);
    }

}

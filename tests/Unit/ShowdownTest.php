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

class ShowdownTest extends TestEnvironment
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
    public function a_royal_flush_beats_a_straight_flush()
    {

        $response = $this->gamePlay->initiateStreetActions()->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Ace',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'King',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Eight',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Nine',
                'suit' => 'Spades'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Queen',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Three',
                'suit' => 'Hearts'
            ],
            [
                'rank' => 'Seven',
                'suit' => 'Diamonds'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Jack',
            'suit' => 'Spades'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Ten',
            'suit' => 'Spades'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $response->hand->playerActions->fresh(),
            'handTable' => $response->handTable->fresh()
        ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Royal Flush')->first()->id, $response['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function a_straight_flush_beats_four_of_a_kind()
    {

        $response = $this->gamePlay->initiateStreetActions()->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Ace',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Ace',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Eight',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Nine',
                'suit' => 'Spades'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Ten',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Ace',
                'suit' => 'Clubs'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Seven',
            'suit' => 'Spades'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Ace',
            'suit' => 'Hearts'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $response->hand->playerActions->fresh(),
            'handTable' => $response->handTable->fresh()
        ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player1->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Straight Flush')->first()->id, $response['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function four_of_a_kind_beats_a_full_house()
    {

        $response = $this->gamePlay->initiateStreetActions()->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Ace',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Ace',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Eight',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Nine',
                'suit' => 'Spades'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Eight',
                'suit' => 'Diamonds'
            ],
            [
                'rank' => 'Eight',
                'suit' => 'Hearts'
            ],
            [
                'rank' => 'Ace',
                'suit' => 'Clubs'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Nine',
            'suit' => 'Clubs'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Ace',
            'suit' => 'Hearts'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $response->hand->playerActions->fresh(),
            'handTable' => $response->handTable->fresh()
        ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Four of a Kind')->first()->id, $response['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function a_full_house_beats_a_flush()
    {

        $response = $this->gamePlay->initiateStreetActions()->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'Ace',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Ace',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Eight',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Nine',
                'suit' => 'Spades'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Four',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Ten',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Ace',
                'suit' => 'Clubs'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Ten',
            'suit' => 'Diamonds'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Three',
            'suit' => 'Spades'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $response->hand->playerActions->fresh(),
            'handTable' => $response->handTable->fresh()
        ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Full House')->first()->id, $response['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function a_flush_beats_a_straight()
    {

        $response = $this->gamePlay->initiateStreetActions()->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player1,
                'rank' => 'Ace',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'King',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Eight',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Nine',
                'suit' => 'Spades'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Queen',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Ten',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Ace',
                'suit' => 'Clubs'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Jack',
            'suit' => 'Diamonds'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Three',
            'suit' => 'Spades'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $response->hand->playerActions->fresh(),
            'handTable' => $response->handTable->fresh()
        ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Flush')->first()->id, $response['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function a_straight_beats_three_of_a_kind()
    {

        $response = $this->gamePlay->initiateStreetActions()->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'King',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Queen',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Seven',
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
                'rank' => 'Jack',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Ten',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Seven',
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
            'rank' => 'Three',
            'suit' => 'Spades'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $response->hand->playerActions->fresh(),
            'handTable' => $response->handTable->fresh()
        ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Straight')->first()->id, $response['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function three_of_a_kind_beats_two_pair()
    {

        $response = $this->gamePlay->initiateStreetActions()->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'King',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Queen',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Seven',
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
                'rank' => 'King',
                'suit' => 'Clubs'
            ],
            [
                'rank' => 'Queen',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Seven',
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
            'rank' => 'Three',
            'suit' => 'Spades'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $response->hand->playerActions->fresh(),
            'handTable' => $response->handTable->fresh()
        ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player1->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Three of a Kind')->first()->id, $response['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function two_pair_beats_a_pair()
    {

        $response = $this->gamePlay->initiateStreetActions()->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player3,
                'rank' => 'King',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Queen',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player1,
                'rank' => 'King',
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
                'rank' => 'King',
                'suit' => 'Clubs'
            ],
            [
                'rank' => 'Queen',
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
            'rank' => 'Three',
            'suit' => 'Spades'
        ];

        $this->setRiver($riverCard);

        $this->executeActions([
            'actions' => $response->hand->playerActions->fresh(),
            'handTable' => $response->handTable->fresh()
        ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Two Pair')->first()->id, $response['winner']['handType']->id);

    }

    /**
     * @test
     * @return void
     */
    public function a_pair_beats_high_card()
    {

        $response = $this->gamePlay->initiateStreetActions()->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player1,
                'rank' => 'King',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Queen',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Ace',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Seven',
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
                'rank' => 'Three',
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
            'actions' => $response->hand->playerActions->fresh(),
            'handTable' => $response->handTable->fresh()
        ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player1->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Pair')->first()->id, $response['winner']['handType']->id);

    }

    /**
     * This test replicates a situation that came up during front-end testing.
     *
     * @test
     * @return void
     */
    public function if_player_one_has_trips_and_player_two_has_pair_these_are_cleared_before_player_three_is_evaluated_so_player_three_doesnt_have_a_full_house()
    {

        $response = $this->gamePlay->initiateStreetActions()->setDealerAndBlindSeats();

        $wholeCards = [
            [
                'player' => $this->player1,
                'rank' => 'Ace',
                'suit' => 'Clubs'
            ],
            [
                'player' => $this->player1,
                'rank' => 'Ace',
                'suit' => 'Hearts'
            ],
            [
                'player' => $this->player2,
                'rank' => 'Three',
                'suit' => 'Hearts'
            ],
            [
                'player' => $this->player2,
                'rank' => 'Five',
                'suit' => 'Spades'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Seven',
                'suit' => 'Diamonds'
            ],
            [
                'player' => $this->player3,
                'rank' => 'Six',
                'suit' => 'Spades'
            ],
        ];

        $this->setWholeCards($wholeCards);

        $flopCards = [
            [
                'rank' => 'Ten',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Deuce',
                'suit' => 'Spades'
            ],
            [
                'rank' => 'Three',
                'suit' => 'Spades'
            ]
        ];

        $this->setFlop($flopCards);

        $turnCard = [
            'rank' => 'Ace',
            'suit' => 'Diamonds'
        ];

        $this->setTurn($turnCard);

        $riverCard = [
            'rank' => 'Five',
            'suit' => 'Clubs'
        ];

        $this->setRiver($riverCard);

        // Player 1 Calls BB
        PlayerAction::where('id', $response->hand->playerActions->fresh()->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1
            ]);

        TableSeat::where('id', $response->handTable->fresh()->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 2 Calls
        PlayerAction::where('id', $response->hand->playerActions->slice(1, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1
            ]);

        TableSeat::where('id', $response->handTable->fresh()->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 3 Checks
        PlayerAction::where('id', $response->hand->playerActions->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Check')->first()->id,
                'bet_amount' => null,
                'active' => 1
            ]);

        TableSeat::where('id', $response->handTable->fresh()->tableSeats->slice(2, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player1->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Three of a Kind')->first()->id, $response['winner']['handType']->id);

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

    protected function executeActions($response)
    {
        // Player 1 Calls BB
        PlayerAction::where('id', $response['actions']->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 2 Folds
        PlayerAction::where('id', $response['actions']->slice(1, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Fold')->first()->id,
                'bet_amount' => null,
                'active' => 0
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        // Player 3 Checks
        PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Check')->first()->id,
                'bet_amount' => null,
                'active' => 1
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(2, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);
    }

}

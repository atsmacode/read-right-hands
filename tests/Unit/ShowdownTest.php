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

        $response = $this->gamePlay->initiateStreetActions()->postBlinds();

        WholeCard::factory([
            'player_id' => $this->player3->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', 'Ace')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            'hand_id' => $this->gamePlay->hand->id
        ])->create();

        WholeCard::factory([
            'player_id' => $this->player3->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', 'King')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            'hand_id' => $this->gamePlay->hand->id
        ])->create();

        WholeCard::factory([
            'player_id' => $this->player2->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', 'Eight')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            'hand_id' => $this->gamePlay->hand->id
        ])->create();

        WholeCard::factory([
            'player_id' => $this->player2->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', 'Nine')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first(),
            'hand_id' => $this->gamePlay->hand->id
        ])->create();

        $flop = HandStreet::factory()->create([
            'street_id' => Street::where('name', $this->gamePlay->game->streets[1]['name'])->first()->id,
            'hand_id' => $this->gamePlay->hand->id
        ]);

        HandStreetCard::factory()->create([
            'hand_street_id' => $flop->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', 'Queen')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ]);

        HandStreetCard::factory()->create([
            'hand_street_id' => $flop->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', 'Three')->first()->id,
                'suit_id' => Suit::where('name', 'Hearts')->first()->id
            ])->first()
        ]);

        HandStreetCard::factory()->create([
            'hand_street_id' => $flop->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', 'Seven')->first()->id,
                'suit_id' => Suit::where('name', 'Diamonds')->first()->id
            ])->first()
        ]);

        $turn = HandStreet::factory()->create([
            'street_id' => Street::where('name', $this->gamePlay->game->streets[2]['name'])->first()->id,
            'hand_id' => $this->gamePlay->hand->id
        ]);

        HandStreetCard::factory()->create([
            'hand_street_id' => $turn->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', 'Jack')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ]);

        $river = HandStreet::factory()->create([
            'street_id' => Street::where('name', $this->gamePlay->game->streets[3]['name'])->first()->id,
            'hand_id' => $this->gamePlay->hand->id
        ]);

        HandStreetCard::factory()->create([
            'hand_street_id' => $river->id,
            'card_id' => Card::where([
                'rank_id' => Rank::where('name', 'Ten')->first()->id,
                'suit_id' => Suit::where('name', 'Spades')->first()->id
            ])->first()
        ]);

        $this->executeActions([
            'actions' => $response->hand->playerActions->fresh(),
            'handTable' => $response->handTable->fresh()
        ]);

        $response = $this->gamePlay->play();

        $this->assertEquals($this->player3->id, $response['winner']['player']->id);
        $this->assertEquals($this->handTypes->where('name', 'Royal Flush')->first()->id, $response['winner']['handType']->id);

    }

    protected function executeActions($response)
    {
        // Player 3 Calls BB
        PlayerAction::where('id', $response['actions']->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(2, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 1 Folds
        PlayerAction::where('id', $response['actions']->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Fold')->first()->id,
                'bet_amount' => null,
                'active' => 0
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        // Player 2 Checks
        PlayerAction::where('id', $response['actions']->slice(1, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Check')->first()->id,
                'bet_amount' => null,
                'active' => 1
            ]);

        TableSeat::where('id', $response['handTable']->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);
    }

}

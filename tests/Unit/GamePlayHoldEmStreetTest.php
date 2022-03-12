<?php

namespace Tests\Unit;

use App\Classes\GamePlay;
use App\Models\Action;
use App\Models\Hand;
use App\Models\HandStreet;
use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\Street;
use App\Models\Table;
use App\Models\TableSeat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;

class GamePlayHoldEmStreetTest extends TestEnvironment
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->table = Table::factory([
            'name' => 'Table 1',
            'seats' => 3
        ])->create();

        $this->player1 = Player::factory()->create();
        $this->player2 = Player::factory()->create();
        $this->player3 = Player::factory()->create();

        TableSeat::factory([
            'table_id' => $this->table->id,
            'player_id' => $this->player1->id
        ])->create();

        TableSeat::factory([
            'table_id' => $this->table->id,
            'player_id' => $this->player2->id
        ])->create();

        TableSeat::factory([
            'table_id' => $this->table->id,
            'player_id' => $this->player3->id
        ])->create();

        $this->gamePlay = new GamePlay(Hand::create());
    }

     /**
     * @test
     * @return void
     */
    public function it_can_deal_3_cards_to_a_flop()
    {
        $response = $this->gamePlay->start();

        $this->executeActions($response);

        $response = $this->gamePlay->play();

        $this->assertCount(2, $response['hand']->streets);
        $this->assertCount(3, $response['hand']->streets->slice(1, 1)->first()->cards);

    }

    /**
     * @test
     * @return void
     */
    public function it_can_deal_1_card_to_a_turn()
    {
        $response = $this->gamePlay->start();

        $this->setFlop($response);

        $this->executeActions($response);

        $response = $this->gamePlay->play();

        $this->assertCount(3, $response['hand']->streets);
        $this->assertCount(1, $response['hand']->streets->slice(2, 1)->first()->cards);

    }

    /**
     * @test
     * @return void
     */
    public function it_can_deal_1_card_to_a_river()
    {
        $response = $this->gamePlay->start();

        $this->setFlop($response);

        $this->setTurn($response);

        $this->executeActions($response);

        $response = $this->gamePlay->play();

        $this->assertCount(4, $response['hand']->streets);
        $this->assertCount(1, $response['hand']->streets->slice(3, 1)->first()->cards);

    }

    /**
     * @test
     * @return void
     */
    public function it_can_reach_showdown_when_all_active_players_can_continue_on_the_river()
    {
        $response = $this->gamePlay->start();

        $this->setFlop($response);

        $this->setTurn($response);

        $this->setRiver($response);

        $this->executeActions($response);

        $response = $this->gamePlay->play();

        $this->assertNotNull($response['winner']);

    }

    protected function setFlop($response)
    {
        // Manually set the flop
        $flop = HandStreet::create([
            'street_id' => Street::where('name', 'Flop')->first()->id,
            'hand_id' => $response['hand']->id
        ]);

        $dealtCards = 0;
        while($dealtCards < $this->gamePlay->game->streets[1]['community_cards']){
            $this->gamePlay->dealer->dealStreetCard($flop);
            $dealtCards++;
        }
    }

    protected function setTurn($response)
    {
        // Manually set the turn
        $turn = HandStreet::create([
            'street_id' => Street::where('name', 'Turn')->first()->id,
            'hand_id' => $response['hand']->id
        ]);

        $dealtCards = 0;
        while($dealtCards < $this->gamePlay->game->streets[2]['community_cards']){
            $this->gamePlay->dealer->dealStreetCard($turn);
            $dealtCards++;
        }
    }

    protected function setRiver($response)
    {
        // Manually set the river
        $river = HandStreet::create([
            'street_id' => Street::where('name', 'River')->first()->id,
            'hand_id' => $response['hand']->id
        ]);

        $dealtCards = 0;
        while($dealtCards < $this->gamePlay->game->streets[3]['community_cards']){
            $this->gamePlay->dealer->dealStreetCard($river);
            $dealtCards++;
        }
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

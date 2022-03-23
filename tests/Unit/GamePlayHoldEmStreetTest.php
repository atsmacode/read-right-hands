<?php

namespace Tests\Unit;

use App\Classes\GamePlay;
use App\Helpers\BetHelper;
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

    }

     /**
     * @test
     * @return void
     */
    public function it_can_deal_3_cards_to_a_flop()
    {
        $this->gamePlay->start();

        $this->executeActions();

        $this->gamePlay->play();

        $this->assertCount(2, $this->gamePlay->hand->fresh()->streets);
        $this->assertCount(3, $this->gamePlay->hand->fresh()->streets->slice(1, 1)->first()->cards);

    }

    /**
     * @test
     * @return void
     */
    public function it_can_deal_1_card_to_a_turn()
    {
        $this->gamePlay->start();

        $this->setFlop();

        $this->executeActions();

        $response = $this->gamePlay->play();

        $this->assertCount(3, $this->gamePlay->hand->fresh()->streets);
        $this->assertCount(1, $this->gamePlay->hand->fresh()->streets->slice(2, 1)->first()->cards);
        $this->assertTrue($response['players'][2]['action_on']);

    }

    /**
     * @test
     * @return void
     */
    public function it_can_deal_1_card_to_a_river()
    {
        $this->gamePlay->start();

        $this->setFlop();

        $this->setTurn();

        $this->executeActions();

        $response = $this->gamePlay->play();

        $this->assertCount(4, $this->gamePlay->hand->fresh()->streets);
        $this->assertCount(1, $this->gamePlay->hand->fresh()->streets->slice(3, 1)->first()->cards);
        $this->assertTrue($response['players'][2]['action_on']);

    }

    /**
     * @test
     * @return void
     */
    public function it_can_reach_showdown_when_all_active_players_can_continue_on_the_river()
    {
        $this->gamePlay->start();

        $this->setFlop();

        $this->setTurn();

        $this->setRiver();

        $this->executeActions();

        $response = $this->gamePlay->play();

        $this->assertNotNull($response['winner']);

    }

    protected function setFlop()
    {
        // Manually set the flop
        $flop = HandStreet::create([
            'street_id' => Street::where('name', 'Flop')->first()->id,
            'hand_id' => $this->gamePlay->hand->fresh()->id
        ]);

        $this->gamePlay->dealer->dealStreetCards(
            $flop,
            $this->gamePlay->game->streets[1]['community_cards']
        );
    }

    protected function setTurn()
    {
        // Manually set the turn
        $turn = HandStreet::create([
            'street_id' => Street::where('name', 'Turn')->first()->id,
            'hand_id' => $this->gamePlay->hand->fresh()->id
        ]);

        $this->gamePlay->dealer->dealStreetCards(
            $turn,
            $this->gamePlay->game->streets[2]['community_cards']
        );
    }

    protected function setRiver()
    {
        // Manually set the river
        $river = HandStreet::create([
            'street_id' => Street::where('name', 'River')->first()->id,
            'hand_id' => $this->gamePlay->hand->fresh()->id
        ]);

        $this->gamePlay->dealer->dealStreetCards(
            $river,
            $this->gamePlay->game->streets[3]['community_cards']
        );
    }

    protected function executeActions()
    {
        // Player 1 Calls BB
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(0, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Call')->first()->id,
                'bet_amount' => 50.0,
                'active' => 1
            ]);

        TableSeat::where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(0, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

        // Player 2 Folds
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(1, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Fold')->first()->id,
                'bet_amount' => null,
                'active' => 0
            ]);

        TableSeat::where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(1, 1)->first()->id)
            ->update([
                'can_continue' => 0
            ]);

        // Player 3 Checks
        PlayerAction::where('id', $this->gamePlay->hand->playerActions->fresh()->slice(2, 1)->first()->id)
            ->update([
                'action_id' => Action::where('name', 'Check')->first()->id,
                'bet_amount' => null,
                'active' => 1
            ]);

        TableSeat::where('id', $this->gamePlay->handTable->fresh()->tableSeats->slice(2, 1)->first()->id)
            ->update([
                'can_continue' => 1
            ]);

    }

}

<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\TableSeat;
use App\Models\Table;
use Illuminate\Console\Command;

class CreatePlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:players';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the table seats with players';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        TableSeat::factory([
            'table_id' => Table::first()->id,
            'player_id' => Player::factory()->create()
        ])->create();

        TableSeat::factory([
            'table_id' => Table::first()->id,
            'player_id' => Player::factory()->create()
        ])->create();

        TableSeat::factory([
            'table_id' => Table::first()->id,
            'player_id' => Player::factory()->create()
        ])->create();

        return 0;
    }
}

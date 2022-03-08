<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Models\Rank;
use App\Models\Suit;
use Illuminate\Console\Command;

class CreateCardDeck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:deck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the cards table with all 52 cards';

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
        $suits = Suit::all();
        $ranks = Rank::all();

        foreach($suits as $suit){
            foreach($ranks as $rank){
                Card::factory([
                    'rank_id' => $rank->id,
                    'suit_id' => $suit->id
                ])->create();
            }
        }

        return 0;

    }
}

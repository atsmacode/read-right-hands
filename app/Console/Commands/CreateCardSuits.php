<?php

namespace App\Console\Commands;

use App\Models\Suit;
use Illuminate\Console\Command;

class CreateCardSuits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:suits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the database with all required suits';

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
        $ranks = config('suits');

        foreach($ranks as $rank){
            Suit::factory([
                'name' => $rank['name'],
                'abbreviation' => $rank['abbreviation']
            ])->create();
        }

        return 0;
    }
}

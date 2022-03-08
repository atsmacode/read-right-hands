<?php

namespace App\Console\Commands;

use App\Models\Rank;
use Illuminate\Console\Command;

class CreateCardRanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:ranks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the database with all card ranks';

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

        $ranks = config('ranks');

        foreach($ranks as $rank){
            Rank::factory([
                'name' => $rank['name'],
                'abbreviation' => $rank['abbreviation'],
                'ranking' => $rank['ranking'],
            ])->create();
        }

        return 0;

    }
}

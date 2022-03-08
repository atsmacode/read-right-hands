<?php

namespace App\Console\Commands;

use App\Models\HandType;
use Illuminate\Console\Command;

class CreateHandTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:handtypes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the database with all the possible hand types and their rankings';

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
        $handTypes = config('handtypes');

        foreach($handTypes as $handType){
            HandType::factory([
                'name' => $handType['name'],
                'ranking' => $handType['ranking']
            ])->create();
        }

        return 0;
    }
}

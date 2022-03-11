<?php

namespace App\Console\Commands;

use App\Models\Street;
use Illuminate\Console\Command;

class CreateStreets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:streets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the database with the basic streets';

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

        Street::factory([
            'name' => 'pre_flop'
        ])->create();

        Street::factory([
            'name' => 'flop'
        ])->create();

        Street::factory([
            'name' => 'turn'
        ])->create();

        Street::factory([
            'name' => 'river'
        ])->create();

        return 0;
    }
}

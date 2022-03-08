<?php

namespace App\Console\Commands;

use App\Models\Action;
use Illuminate\Console\Command;

class CreateActions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:actions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the database with all the possible actions a player can make';

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
        $actions = config('actions');

        foreach($actions as $action){
            Action::factory([
                'name' => $action
            ])->create();
        }

        return 0;
    }
}

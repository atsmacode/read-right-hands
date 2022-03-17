<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class BuildEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:env';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the DB with all the data required to run the app';

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

        Artisan::call('migrate:fresh');
        Artisan::call('create:ranks');
        Artisan::call('create:suits');
        Artisan::call('create:deck');
        Artisan::call('create:table');
        Artisan::call('create:players');
        Artisan::call('create:streets');
        Artisan::call('create:actions');
        Artisan::call('create:handtypes');

        return 0;
    }
}

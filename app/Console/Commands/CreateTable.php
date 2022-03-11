<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\PlayerAction;
use App\Models\Table;
use App\Models\TableSeat;
use Illuminate\Console\Command;

class CreateTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the database with a table and it\'s associated seats';

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
     * @return int
     */
    public function handle()
    {
        $table = Table::factory([
            'name' => 'Table 1',
            'seats' => 3
        ])->create();

        TableSeat::factory([
            'table_id' => $table->id
        ])->count(3)->create();

        return 0;
    }
}

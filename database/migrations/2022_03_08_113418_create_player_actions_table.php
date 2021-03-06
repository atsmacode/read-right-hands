<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('action_id')->unsigned()->nullable();
            $table->foreign('action_id')->references('id')->on('actions');
            $table->integer('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('players');
            $table->integer('hand_id')->unsigned();
            $table->foreign('hand_id')->references('id')->on('hands');
            $table->integer('hand_street_id')->unsigned();
            $table->foreign('hand_street_id')->references('id')->on('hand_streets');
            $table->integer('table_seat_id')->unsigned();
            $table->foreign('table_seat_id')->references('id')->on('table_seats');
            $table->float('bet_amount')->nullable();
            $table->boolean('active')->default(0);
            $table->boolean('big_blind')->default(0);
            $table->boolean('small_blind')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_actions');
    }
}

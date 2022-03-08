<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandWinnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hand_winners', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hand_id')->unsigned();
            $table->foreign('hand_id')->references('id')->on('hands');
            $table->integer('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('players');
            $table->integer('hand_type_id')->unsigned();
            $table->foreign('hand_type_id')->references('id')->on('hand_types');
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
        Schema::dropIfExists('hand_winners');
    }
}

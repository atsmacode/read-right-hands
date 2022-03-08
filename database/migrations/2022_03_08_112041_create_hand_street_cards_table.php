<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandStreetCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hand_street_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hand_street_id');
            $table->foreign('hand_street_id')->references('id')->on('hand_streets');
            $table->integer('card_id');
            $table->foreign('card_id')->references('id')->on('cards');
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
        Schema::dropIfExists('hand_street_cards');
    }
}

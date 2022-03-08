<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWholeCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whole_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('card_id');
            $table->foreign('card_id')->references('id')->on('cards');
            $table->integer('hand_id');
            $table->foreign('hand_id')->references('id')->on('hands');
            $table->integer('player_id');
            $table->foreign('player_id')->references('id')->on('players');
            $table->boolean('active')->default(1);
            $table->boolean('can_continue')->default(1);
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
        Schema::dropIfExists('whole_cards');
    }
}

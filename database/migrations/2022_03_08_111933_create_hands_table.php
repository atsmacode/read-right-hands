<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hands', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('completed_on')->nullable();
            $table->integer('table_id')->unsigned()->nullable();
            $table->foreign('table_id')->references('id')->on('tables');
            $table->integer('game_type_id')->unsigned()->nullable();
            $table->foreign('game_type_id')->references('id')->on('game_types');
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
        Schema::dropIfExists('hands');
    }
}

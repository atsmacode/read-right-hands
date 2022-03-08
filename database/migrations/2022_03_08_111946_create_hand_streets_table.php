<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandStreetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hand_streets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('street_id')->unsigned();
            $table->foreign('street_id')->references('id')->on('streets');
            $table->integer('hand_id')->unsigned();
            $table->foreign('hand_id')->references('id')->on('hands');
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
        Schema::dropIfExists('hand_streets');
    }
}

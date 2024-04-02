<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLifeGaugesTable extends Migration
{
    public function up()
    {
        Schema::create('life_gauges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('perso_id');
            $table->tinyInteger('hunger')->unsigned();
            $table->tinyInteger('thirst')->unsigned();
            $table->tinyInteger('clean')->unsigned();
            $table->tinyInteger('happiness')->unsigned();
            $table->tinyInteger('entertainment')->unsigned();
            $table->tinyInteger('physical_condition')->unsigned();
            $table->tinyInteger('health')->unsigned();
            $table->timestamps();


            $table->foreign('perso_id')->references('id')->on('perso')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('life_gauges');
    }
}

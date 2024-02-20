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
            $table->tinyInteger('hunger')->unsigned(); // TINYINT UNSIGNED NOT NULL
            $table->tinyInteger('thirst')->unsigned();
            $table->tinyInteger('clean')->unsigned();
            $table->tinyInteger('happiness')->unsigned();
            $table->tinyInteger('health')->unsigned();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('life_gauges');
    }
}

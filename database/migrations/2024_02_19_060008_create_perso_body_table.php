<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersoBodyTable extends Migration
{
    public function up()
    {
        Schema::create('perso_bodies', function (Blueprint $table) {
            $table->id();
            $table->string('img_perso');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perso_bodies');
    }
}

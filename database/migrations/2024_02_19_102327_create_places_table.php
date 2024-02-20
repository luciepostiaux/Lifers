<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlacesTable extends Migration
{
    public function up()
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('img', 255); // Chemin de l'image
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('places');
    }
}

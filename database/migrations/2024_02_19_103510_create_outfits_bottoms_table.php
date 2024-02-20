<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutfitsBottomsTable extends Migration
{
    public function up()
    {
        Schema::create('outfits_bottoms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('description', 1500);
            $table->float('price'); // Utilisez decimal pour une précision financière
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('outfits_bottoms');
    }
}

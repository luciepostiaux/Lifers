<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutfitsTopsTable extends Migration
{
    public function up()
    {
        Schema::create('outfits_tops', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('description', 1500);
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('outfits_tops');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryOutfitBottomsOutfitsBottomsTable extends Migration
{
    public function up()
    {
        Schema::create('inventories_outfits_bottoms_has_outfits_bottoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventories_outfits_bottoms_id')->constrained()->onDelete('cascade');
            $table->foreignId('outfits_bottoms_id')->constrained()->onDelete('cascade');
            $table->foreignId('colors_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories_outfits_bottoms_has_outfits_bottoms');
    }
}

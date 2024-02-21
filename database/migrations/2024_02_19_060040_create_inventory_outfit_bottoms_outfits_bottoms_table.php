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
            // Définir les clés étrangères avec des noms personnalisés pour éviter la limite de longueur
            $table->unsignedBigInteger('inventories_outfits_bottoms_id');
            $table->foreign('inventories_outfits_bottoms_id', 'inv_outfits_bottoms_fk')
                ->references('id')->on('inventories_outfits_bottoms')->onDelete('cascade');

            $table->unsignedBigInteger('outfits_bottoms_id');
            $table->foreign('outfits_bottoms_id', 'outfits_bottoms_fk')
                ->references('id')->on('outfits_bottoms')->onDelete('cascade');

            $table->unsignedBigInteger('colors_id');
            $table->foreign('colors_id', 'colors_fk')
                ->references('id')->on('colors')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories_outfits_bottoms_has_outfits_bottoms');
    }
}

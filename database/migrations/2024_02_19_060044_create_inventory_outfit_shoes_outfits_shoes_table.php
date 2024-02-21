<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryOutfitShoesOutfitsShoesTable extends Migration
{
    public function up()
    {
        Schema::create('inventories_outfits_shoes_has_outfits_shoes', function (Blueprint $table) {
            $table->id();
            // Utilisez la méthode foreign() pour créer manuellement la contrainte avec un nom plus court
            $table->unsignedBigInteger('inventories_outfits_shoes_id');
            $table->foreign('inventories_outfits_shoes_id', 'inv_outfits_shoes_fk')
                ->references('id')->on('inventories_outfits_shoes')->onDelete('cascade');

            $table->unsignedBigInteger('outfits_shoes_id');
            $table->foreign('outfits_shoes_id', 'outfits_shoes_fk')
                ->references('id')->on('outfits_shoes')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories_outfits_shoes_has_outfits_shoes');
    }
}

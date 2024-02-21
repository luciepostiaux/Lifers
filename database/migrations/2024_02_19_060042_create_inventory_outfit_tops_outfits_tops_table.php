<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryOutfitTopsOutfitsTopsTable extends Migration
{
    public function up()
    {
        Schema::create('inventories_outfits_tops_has_outfits_tops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventories_outfits_tops_id');
            $table->foreignId('outfits_tops_id');
            $table->foreignId('colors_id');
            $table->timestamps();

            // Spécifier des noms uniques pour chaque contrainte de clé étrangère
            $table->foreign('inventories_outfits_tops_id', 'inv_outfits_tops_fk')->references('id')->on('inventories_outfits_tops')->onDelete('cascade');
            $table->foreign('outfits_tops_id', 'outfits_tops_fk')->references('id')->on('outfits_tops')->onDelete('cascade');
            // Assurez-vous que le nom de la contrainte 'colors_fk' est unique
            $table->foreign('colors_id', 'inv_outfits_tops_colors_fk')->references('id')->on('colors')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories_outfits_tops_has_outfits_tops');
    }
}

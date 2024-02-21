<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersoOutfitsTable extends Migration
{
    public function up()
    {
        Schema::create('perso_outfits', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inventories_outfits_bottoms_id')->nullable();
            $table->unsignedBigInteger('inventories_outfits_tops_id')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perso_outfits');
    }
}

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
            $table->foreignId('inventories_outfits_bottoms_id')->constrained()->onDelete('no action')->onUpdate('no action');
            $table->foreignId('inventories_outfits_tops_id')->constrained()->onDelete('no action')->onUpdate('no action');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perso_outfits');
    }
}

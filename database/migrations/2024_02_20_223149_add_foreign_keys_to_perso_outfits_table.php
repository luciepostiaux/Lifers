<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPersoOutfitsTable extends Migration
{
    public function up()
    {
        Schema::table('perso_outfits', function (Blueprint $table) {
            $table->foreign('inventories_outfits_bottoms_id')->references('id')->on('inventories_outfits_bottoms')->onDelete('no action')->onUpdate('no action');
            $table->foreign('inventories_outfits_tops_id')->references('id')->on('inventories_outfits_tops')->onDelete('no action')->onUpdate('no action');
        });
    }

    public function down()
    {
        Schema::table('perso_outfits', function (Blueprint $table) {
            $table->dropForeign(['inventories_outfits_bottoms_id']);
            $table->dropForeign(['inventories_outfits_tops_id']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesOutfitsTopsTable extends Migration
{
    public function up()
    {
        Schema::create('inventories_outfits_tops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventories_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories_outfits_tops');
    }
}


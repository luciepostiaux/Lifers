<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHairstylesTable extends Migration
{
    public function up()
    {
        Schema::create('hairstyles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('description', 1500);
            $table->unsignedDecimal('price'); // DECIMAL UNSIGNED NOT NULL
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hairstyles');
    }
}

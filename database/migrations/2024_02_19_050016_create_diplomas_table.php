<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiplomasTable extends Migration
{
    public function up()
    {
        Schema::create('diplomas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('description', 500);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diplomas');
    }
}

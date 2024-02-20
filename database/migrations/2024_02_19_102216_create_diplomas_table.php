<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiplomasTable extends Migration
{
    public function up()
    {
        Schema::create('diplomas', function (Blueprint $table) {
            $table->id(); // INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY
            $table->string('name', 45); // VARCHAR(45) NOT NULL
            $table->string('description', 1500); // VARCHAR(1500) NOT NULL
            $table->timestamps(); // Crée automatiquement `created_at` et `updated_at`
        });
    }

    public function down()
    {
        Schema::dropIfExists('diplomas');
    }
}

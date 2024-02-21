<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersoHasDiplomasTable extends Migration
{
    public function up()
    {
        Schema::create('perso_has_diplomas', function (Blueprint $table) {
            // Assurez-vous que le nom de la table référencée correspond exactement.
            $table->foreignId('perso_id')->constrained('perso')->onDelete('cascade');
            $table->foreignId('diplomas_id')->constrained()->onDelete('cascade');

            $table->primary(['perso_id', 'diplomas_id']); // Définit une clé primaire composite.

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perso_has_diplomas');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('pseudo', 45);
            $table->string('password', 45); // Considérez l'utilisation de hash pour les mots de passe
            $table->date('created_at');
            $table->foreignId('perso_id')->constrained()->onDelete('no action')->onUpdate('no action');
            $table->boolean('consentement_newsletter')->nullable();
            $table->dateTime('date_consentement')->nullable();
            $table->boolean('consentement_rgpd')->nullable();
            // Laravel gère automatiquement les colonnes created_at et updated_at si vous utilisez $table->timestamps();
            // Donc, si vous n'avez pas besoin d'une logique personnalisée pour 'created_at', il est recommandé d'utiliser $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}

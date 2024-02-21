<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('pseudo', 45);
            $table->string('email')->unique(); // Ajoutez cette ligne pour le champ email
            $table->string('password', 45); // Considérez l'utilisation de hash pour les mots de passe
            $table->date('created_at');
            $table->boolean('consentement_newsletter')->nullable();
            $table->dateTime('date_consentement')->nullable();
            $table->boolean('consentement_rgpd')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

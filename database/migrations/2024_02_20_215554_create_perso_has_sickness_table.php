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
        Schema::create('perso_has_sickness', function (Blueprint $table) {
            $table->foreignId('perso_id')->constrained('perso')->onDelete('cascade');
            $table->foreignId('sickness_id')->constrained('sickness')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perso_has_sickness');
    }
};

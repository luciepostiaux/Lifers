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
        Schema::create('perso_residences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perso_id')->constrained('perso')->onDelete('cascade');
            $table->foreignId('residence_id')->constrained('residences')->onDelete('cascade');
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perso_residences');
    }
};

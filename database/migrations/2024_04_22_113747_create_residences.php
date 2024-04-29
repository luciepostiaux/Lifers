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
        Schema::create('residences', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->decimal('prix_achat', 8, 2);
            $table->decimal('prix_mensuel', 8, 2)->nullable();
            $table->decimal('taxe_mensuelle', 8, 2)->nullable();
            $table->tinyInteger('nombre_paiements')->default(1);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();

            
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residences');
    }
};

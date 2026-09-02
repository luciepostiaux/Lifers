<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lifer_family_favorites', function (Blueprint $table) {
            $table->foreignId('owner_lifer_id')
                ->constrained('lifer_game_states', 'lifer_id')
                ->cascadeOnDelete();
            $table->foreignId('favorite_lifer_id')
                ->constrained('lifer_game_states', 'lifer_id')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['owner_lifer_id', 'favorite_lifer_id']);
            $table->index(['owner_lifer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lifer_family_favorites');
    }
};

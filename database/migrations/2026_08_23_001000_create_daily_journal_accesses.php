<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_journal_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->date('access_date');
            $table->unsignedInteger('price_paid');
            $table->dateTime('purchased_at');
            $table->timestamps();

            $table->unique(['lifer_id', 'access_date']);
            $table->index('access_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_journal_accesses');
    }
};

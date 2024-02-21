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
        Schema::create('sickness_has_sickness_gauges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sickness_id')->constrained('sickness')->onDelete('cascade');
            $table->foreignId('sickness_gauges_id')->constrained('sickness_gauges')->onDelete('cascade');

            $table->integer('effect_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sickness_has_sickness_gauges');
    }
};

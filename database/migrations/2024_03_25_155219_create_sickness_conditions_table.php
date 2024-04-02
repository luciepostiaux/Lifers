<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sickness_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sickness_id')->constrained('sickness')->onDelete('cascade');
            $table->string('condition_type');
            $table->integer('threshold');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sickness_conditions');
    }
};

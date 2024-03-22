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
        Schema::create('sport_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); 
            $table->integer('price');
            $table->integer('duration'); 
            $table->integer('effect'); 
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sport_sessions');
    }
};

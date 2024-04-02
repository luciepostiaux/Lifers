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
        Schema::create('perso_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('perso_id');
            $table->string('image_path');
            $table->foreign('perso_id')->references('id')->on('perso')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('perso_image');
    }
};

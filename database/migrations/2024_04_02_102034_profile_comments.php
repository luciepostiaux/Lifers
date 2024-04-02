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
        Schema::create('profile_comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment');
            $table->unsignedBigInteger('author_perso_id');
            $table->foreign('author_perso_id')->references('id')->on('perso')->onDelete('cascade');
            $table->unsignedBigInteger('receiver_perso_id');
            $table->foreign('receiver_perso_id')->references('id')->on('perso')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('profile_comments');
    }
};

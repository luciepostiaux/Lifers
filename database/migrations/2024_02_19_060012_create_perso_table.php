<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersoTable extends Migration
{
    public function up()
    {
        Schema::create('perso', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 45);
            $table->string('last_name', 45);
            $table->date('birth_date');
            $table->decimal('money', 10, 2);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('hairstyles_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('perso_outfits_id')->nullable()->constrained()->onDelete('no action');
            $table->foreignId('jobs_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('salary', 10, 2)->nullable();
            $table->foreignId('mother_id')->nullable()->constrained('perso')->onDelete('set null');
            $table->foreignId('father_id')->nullable()->constrained('perso')->onDelete('set null');
            $table->foreignId('partner_id')->nullable()->constrained('perso')->onDelete('set null');
            $table->foreignId('perso_bodies_id')->nullable()->constrained('perso_bodies')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perso');
    }
}

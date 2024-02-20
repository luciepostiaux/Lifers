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
            $table->float('money');
            $table->foreignId('hairstyles_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('life_gauges_id')->constrained()->onDelete('no action');
            $table->foreignId('perso_outfits_id')->constrained()->onDelete('no action');
            $table->foreignId('jobs_id')->nullable()->constrained()->onDelete('set null');
            $table->float('salary')->nullable();
            $table->foreignId('studies_id')->constrained()->onDelete('no action');
            $table->foreignId('mother_id')->nullable()->constrained('perso')->onDelete('set null');
            $table->foreignId('father_id')->nullable()->constrained('perso')->onDelete('set null');
            $table->foreignId('partner_id')->nullable()->constrained('perso')->onDelete('set null');
            $table->foreignId('genders_id')->constrained()->onDelete('no action');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perso');
    }
}

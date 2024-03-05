<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudiesTable extends Migration
{
    public function up()
    {
        Schema::create('studies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('description_1', 1500);
            $table->unsignedDecimal('price', 10, 2);
            $table->integer('duration');
            $table->string('img_study', 255);
            $table->string('description_2', 1500);
            $table->foreignId('diplomas_id')->constrained()->onDelete('no action')->onUpdate('no action');
            $table->foreignId('diplomas_required_id')->nullable()->constrained('diplomas')->onDelete('no action')->onUpdate('no action');
            $table->foreignId('places_id')->constrained()->onDelete('no action')->onUpdate('no action');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('studies');
    }
}

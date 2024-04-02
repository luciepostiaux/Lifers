<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersoStudyEnrollmentsTable extends Migration
{
    public function up()
    {
        Schema::create('perso_study_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perso_id')->constrained('perso')->onDelete('cascade');
            $table->foreignId('study_id')->constrained('studies')->onDelete('cascade');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perso_study_enrollments');
    }
}

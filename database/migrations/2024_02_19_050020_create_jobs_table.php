<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('description_1', 300);
            $table->string('description_2', 750)->nullable();
            $table->unsignedDecimal('salary');
            $table->string('img_job', 255);
            $table->foreignId('diplomas_id')->nullable()->constrained()->onDelete('no action')->onUpdate('no action');
            $table->foreignId('places_id')->nullable()->constrained()->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsActionsTable extends Migration
{
    public function up()
    {
        Schema::create('jobs_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('description', 1500);
            $table->decimal('price', 10, 2);
            $table->integer('chance');
            $table->foreignId('jobs_id')->constrained()->onDelete('no action')->onUpdate('no action');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jobs_actions');
    }
}

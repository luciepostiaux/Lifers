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
        Schema::create('sickness', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('duration')->nullable();
            $table->json('chance')->nullable();
            $table->string('type')->default('aléatoire');
            $table->boolean('needs_doctor')->default(false); 
            $table->boolean('self_resolving')->default(true);
            $table->decimal('treatment_cost', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sickness');
    }
};

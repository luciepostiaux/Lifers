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
        Schema::create('inventories_has_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventories_id')->constrained()->onDelete('cascade');
            $table->foreignId('items_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('quantity')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories_has_items');
    }
};

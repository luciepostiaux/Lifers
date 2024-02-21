<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->decimal('price', 10, 2);
            $table->string('description', 1500);
            $table->string('img_item', 255);
            $table->string('img_background', 255)->nullable();
            $table->string('category', 45);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
}

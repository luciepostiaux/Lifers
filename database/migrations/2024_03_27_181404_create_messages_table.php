<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('sender_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
}

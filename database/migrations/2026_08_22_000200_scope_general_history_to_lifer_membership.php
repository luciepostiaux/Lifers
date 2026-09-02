<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_lifer', function (Blueprint $table) {
            $table->unsignedBigInteger('history_from_message_id')->nullable()->after('lifer_id');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_lifer', function (Blueprint $table) {
            $table->dropColumn('history_from_message_id');
        });
    }
};

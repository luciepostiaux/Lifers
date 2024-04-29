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
        Schema::table('perso_residences', function (Blueprint $table) {
            $table->integer('remaining_payments')->after('residence_id')->default(0);
            $table->date('next_payment_due')->after('remaining_payments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perso_residences', function (Blueprint $table) {
            $table->dropColumn(['remaining_payments', 'next_payment_due']);
        });
    }
};

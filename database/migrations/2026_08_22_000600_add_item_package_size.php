<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedSmallInteger('units_per_purchase')->default(1)->after('price');
        });

        DB::statement('ALTER TABLE items ADD CONSTRAINT items_units_per_purchase_check CHECK (units_per_purchase >= 1)');
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('units_per_purchase');
        });
    }
};

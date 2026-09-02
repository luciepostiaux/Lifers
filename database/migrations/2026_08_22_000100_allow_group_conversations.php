<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE conversations DROP CHECK conversations_type_check');
        DB::statement("ALTER TABLE conversations ADD CONSTRAINT conversations_type_check CHECK (type IN ('general', 'private', 'group'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE conversations DROP CHECK conversations_type_check');
        DB::statement("ALTER TABLE conversations ADD CONSTRAINT conversations_type_check CHECK (type IN ('general', 'private'))");
    }
};

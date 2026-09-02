<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lifer_marriages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('first_lifer_id')->constrained('lifers')->cascadeOnDelete();
            $table->foreignId('second_lifer_id')->constrained('lifers')->cascadeOnDelete();
            $table->unsignedBigInteger('lower_lifer_id');
            $table->unsignedBigInteger('higher_lifer_id');
            $table->string('status', 15)->default('active');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->string('end_reason', 20)->nullable();
            $table->timestamps();

            $table->index(['first_lifer_id', 'status']);
            $table->index(['second_lifer_id', 'status']);
            $table->index(['lower_lifer_id', 'higher_lifer_id', 'status'], 'lifer_marriages_pair_status_index');
        });

        Schema::create('family_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('recipient_lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->string('type', 25);
            $table->string('status', 15)->default('pending');
            $table->json('metadata')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_lifer_id', 'status', 'created_at'], 'family_requests_inbox_index');
            $table->index(['requester_lifer_id', 'status', 'created_at'], 'family_requests_outbox_index');
        });

        Schema::create('lifer_intimacy_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('family_requests')->cascadeOnDelete();
            $table->foreignId('first_lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('second_lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->string('type', 20);
            $table->boolean('conception_succeeded')->default(false);
            $table->date('happened_on');
            $table->timestamps();

            $table->index(['first_lifer_id', 'type', 'happened_on'], 'intimacy_first_daily_index');
            $table->index(['second_lifer_id', 'type', 'happened_on'], 'intimacy_second_daily_index');
        });

        DB::statement("ALTER TABLE lifer_marriages ADD CONSTRAINT lifer_marriages_status_check CHECK (status IN ('active', 'divorced', 'widowed'))");
        DB::statement("ALTER TABLE lifer_marriages ADD CONSTRAINT lifer_marriages_end_reason_check CHECK (end_reason IS NULL OR end_reason IN ('divorce', 'death'))");
        DB::statement('ALTER TABLE lifer_marriages ADD CONSTRAINT lifer_marriages_distinct_lifers_check CHECK (first_lifer_id <> second_lifer_id)');
        DB::statement('ALTER TABLE lifer_marriages ADD CONSTRAINT lifer_marriages_canonical_pair_check CHECK (lower_lifer_id = LEAST(first_lifer_id, second_lifer_id) AND higher_lifer_id = GREATEST(first_lifer_id, second_lifer_id))');
        DB::statement("ALTER TABLE family_requests ADD CONSTRAINT family_requests_type_check CHECK (type IN ('marriage', 'intimacy_protected', 'baby_attempt', 'child_abandonment'))");
        DB::statement("ALTER TABLE family_requests ADD CONSTRAINT family_requests_status_check CHECK (status IN ('pending', 'accepted', 'rejected', 'cancelled'))");
        DB::statement('ALTER TABLE family_requests ADD CONSTRAINT family_requests_distinct_lifers_check CHECK (requester_lifer_id <> recipient_lifer_id)');
        DB::statement("ALTER TABLE lifer_intimacy_events ADD CONSTRAINT lifer_intimacy_type_check CHECK (type IN ('protected', 'baby_attempt'))");
        DB::statement('ALTER TABLE lifer_intimacy_events ADD CONSTRAINT lifer_intimacy_distinct_lifers_check CHECK (first_lifer_id <> second_lifer_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('lifer_intimacy_events');
        Schema::dropIfExists('family_requests');
        Schema::dropIfExists('lifer_marriages');
    }
};

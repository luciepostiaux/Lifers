<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lifers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('first_name', 45);
            $table->string('last_name', 45);
            $table->string('sex', 10);
            $table->dateTime('born_at');
            $table->string('status', 10)->default('active');
            $table->dateTime('died_at')->nullable();
            $table->unsignedSmallInteger('age_at_death')->nullable();
            $table->string('death_cause')->nullable();
            $table->unsignedTinyInteger('active_slot')
                ->nullable()
                ->storedAs("CASE WHEN status = 'active' THEN 1 ELSE NULL END");
            $table->timestamps();

            $table->unique(['user_id', 'active_slot'], 'one_active_lifer_per_user');
            $table->index(['status', 'died_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('lifer_game_states', function (Blueprint $table) {
            $table->foreignId('lifer_id')->primary()->constrained('lifers')->cascadeOnDelete();
            $table->foreignId('body_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('hairstyle_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('money', 12, 2)->unsigned()->default(900);
            $table->text('description')->nullable();
            $table->date('last_gauges_decreased_on')->nullable();
            $table->date('last_sickness_checked_on')->nullable();
            $table->timestamps();
        });

        Schema::create('life_gauges', function (Blueprint $table) {
            $table->foreignId('lifer_id')->primary()->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->unsignedTinyInteger('hunger')->default(100);
            $table->unsignedTinyInteger('thirst')->default(100);
            $table->unsignedTinyInteger('clean')->default(100);
            $table->unsignedTinyInteger('happiness')->default(100);
            $table->unsignedTinyInteger('entertainment')->default(100);
            $table->unsignedTinyInteger('physical_condition')->default(100);
            $table->unsignedTinyInteger('health')->default(100);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE lifers ADD CONSTRAINT lifers_sex_check CHECK (sex IN ('male', 'female'))");
        DB::statement("ALTER TABLE lifers ADD CONSTRAINT lifers_status_check CHECK (status IN ('active', 'dead'))");
        DB::statement("ALTER TABLE lifers ADD CONSTRAINT lifers_death_state_check CHECK ((status = 'active' AND died_at IS NULL AND age_at_death IS NULL AND death_cause IS NULL) OR (status = 'dead' AND died_at IS NOT NULL AND age_at_death IS NOT NULL AND death_cause IS NOT NULL))");
        DB::statement('ALTER TABLE life_gauges ADD CONSTRAINT life_gauges_range_check CHECK (hunger <= 100 AND thirst <= 100 AND clean <= 100 AND happiness <= 100 AND entertainment <= 100 AND physical_condition <= 100 AND health <= 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('life_gauges');
        Schema::dropIfExists('lifer_game_states');
        Schema::dropIfExists('lifers');
    }
};

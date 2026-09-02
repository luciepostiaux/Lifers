<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->foreignId('lifer_id')->primary()->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->foreignId('inventory_id')->constrained('inventories', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->primary(['inventory_id', 'item_id']);
        });

        Schema::create('inventory_wearables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('wearable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('color_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('color_key')->storedAs('COALESCE(color_id, 0)');
            $table->timestamps();

            $table->unique(['inventory_id', 'wearable_id', 'color_key'], 'inventory_wearable_unique');
        });

        Schema::create('equipped_wearables', function (Blueprint $table) {
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->string('slot', 20);
            $table->foreignId('inventory_wearable_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['lifer_id', 'slot']);
            $table->unique('inventory_wearable_id');
        });

        Schema::create('lifer_diplomas', function (Blueprint $table) {
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('diploma_id')->constrained()->restrictOnDelete();
            $table->dateTime('earned_at');

            $table->primary(['lifer_id', 'diploma_id']);
        });

        Schema::create('lifer_study_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('study_id')->constrained()->restrictOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('ends_at');
            $table->dateTime('ended_at')->nullable();
            $table->string('status', 15)->default('active');
            $table->unsignedTinyInteger('active_slot')
                ->nullable()
                ->storedAs("CASE WHEN status = 'active' THEN 1 ELSE NULL END");
            $table->timestamps();

            $table->unique(['lifer_id', 'active_slot'], 'one_active_study_per_lifer');
            $table->index(['lifer_id', 'started_at']);
        });

        Schema::create('lifer_employments', function (Blueprint $table) {
            $table->foreignId('lifer_id')->primary()->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained()->restrictOnDelete();
            $table->dateTime('started_at');
            $table->date('last_salary_paid_on')->nullable();
            $table->timestamps();
        });

        Schema::create('lifer_sicknesses', function (Blueprint $table) {
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('sickness_id')->constrained()->restrictOnDelete();
            $table->dateTime('contracted_at');
            $table->dateTime('expected_recovery_at')->nullable();
            $table->timestamps();

            $table->primary(['lifer_id', 'sickness_id']);
        });

        Schema::create('lifer_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('sport_session_id')->constrained()->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 15)->default('active');
            $table->unsignedTinyInteger('active_slot')
                ->nullable()
                ->storedAs("CASE WHEN status = 'active' THEN 1 ELSE NULL END");
            $table->timestamps();

            $table->unique(['lifer_id', 'active_slot'], 'one_active_subscription_per_lifer');
            $table->index(['lifer_id', 'starts_at']);
        });

        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('animal_type_id')->constrained()->restrictOnDelete();
            $table->string('name', 45);
            $table->dateTime('born_at');
            $table->boolean('is_alive')->default(true);
            $table->dateTime('died_at')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE inventory_items ADD CONSTRAINT inventory_items_quantity_check CHECK (quantity > 0)');
        DB::statement("ALTER TABLE equipped_wearables ADD CONSTRAINT equipped_wearables_slot_check CHECK (slot IN ('top', 'bottom', 'shoes'))");
        DB::statement("ALTER TABLE lifer_study_enrollments ADD CONSTRAINT lifer_study_status_check CHECK (status IN ('active', 'completed', 'left'))");
        DB::statement('ALTER TABLE lifer_study_enrollments ADD CONSTRAINT lifer_study_dates_check CHECK (ends_at >= started_at AND ((status = \'active\' AND ended_at IS NULL) OR (status <> \'active\' AND ended_at IS NOT NULL)))');
        DB::statement("ALTER TABLE lifer_subscriptions ADD CONSTRAINT lifer_subscription_status_check CHECK (status IN ('active', 'cancelled', 'expired'))");
        DB::statement('ALTER TABLE lifer_subscriptions ADD CONSTRAINT lifer_subscription_dates_check CHECK (ends_at >= starts_at)');
        DB::statement('ALTER TABLE animals ADD CONSTRAINT animals_lifecycle_check CHECK ((is_alive = 1 AND died_at IS NULL) OR (is_alive = 0 AND died_at IS NOT NULL))');
    }

    public function down(): void
    {
        Schema::dropIfExists('animals');
        Schema::dropIfExists('lifer_subscriptions');
        Schema::dropIfExists('lifer_sicknesses');
        Schema::dropIfExists('lifer_employments');
        Schema::dropIfExists('lifer_study_enrollments');
        Schema::dropIfExists('lifer_diplomas');
        Schema::dropIfExists('equipped_wearables');
        Schema::dropIfExists('inventory_wearables');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventories');
    }
};

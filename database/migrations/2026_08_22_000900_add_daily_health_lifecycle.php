<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lifer_game_states', function (Blueprint $table) {
            $table->date('vital_red_since')->nullable()->after('last_sickness_checked_on');
            $table->unsignedSmallInteger('vital_green_streak_days')->default(0)->after('vital_red_since');
            $table->date('last_mortality_checked_on')->nullable()->after('vital_green_streak_days');
            $table->date('last_sport_activity_on')->nullable()->after('last_mortality_checked_on');
            $table->date('last_sickness_trigger_checked_on')->nullable()->after('last_sport_activity_on');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->string('usage_tag', 45)->nullable()->after('category')->index();
        });

        Schema::table('sicknesses', function (Blueprint $table) {
            $table->string('slug', 100)->nullable()->after('name')->unique();
            $table->string('effect_timing', 20)->default('once')->after('treatment_cost');
            $table->decimal('daily_decay_multiplier', 4, 2)->unsigned()->default(1)->after('effect_timing');
            $table->unsignedInteger('fatal_after_days')->nullable()->after('daily_decay_multiplier');
            $table->string('trigger_type', 45)->nullable()->after('fatal_after_days');
            $table->unsignedInteger('trigger_days')->nullable()->after('trigger_type');
            $table->json('trigger_config')->nullable()->after('trigger_days');
            $table->json('risk_config')->nullable()->after('trigger_config');
        });

        Schema::table('lifer_sicknesses', function (Blueprint $table) {
            $table->date('last_effect_applied_on')->nullable()->after('expected_recovery_at');
            $table->dateTime('fatal_at')->nullable()->after('last_effect_applied_on');
            $table->index('fatal_at');
        });

        Schema::create('lifer_item_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('usage_tag', 45)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->dateTime('used_at');
            $table->timestamps();

            $table->index(['lifer_id', 'used_at']);
            $table->index(['item_id', 'used_at']);
            $table->index(['lifer_id', 'usage_tag', 'used_at'], 'lifer_item_usage_risk_index');
        });

        Schema::create('lifer_sickness_trigger_states', function (Blueprint $table) {
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('sickness_id')->constrained()->cascadeOnDelete();
            $table->date('started_on');
            $table->date('last_checked_on');
            $table->timestamps();

            $table->primary(['lifer_id', 'sickness_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lifer_sickness_trigger_states');
        Schema::dropIfExists('lifer_item_usages');

        Schema::table('lifer_sicknesses', function (Blueprint $table) {
            $table->dropIndex(['fatal_at']);
            $table->dropColumn(['last_effect_applied_on', 'fatal_at']);
        });

        Schema::table('sicknesses', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug',
                'effect_timing',
                'daily_decay_multiplier',
                'fatal_after_days',
                'trigger_type',
                'trigger_days',
                'trigger_config',
                'risk_config',
            ]);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['usage_tag']);
            $table->dropColumn('usage_tag');
        });

        Schema::table('lifer_game_states', function (Blueprint $table) {
            $table->dropColumn([
                'vital_red_since',
                'vital_green_streak_days',
                'last_mortality_checked_on',
                'last_sport_activity_on',
                'last_sickness_trigger_checked_on',
            ]);
        });
    }
};

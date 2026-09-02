<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_types', function (Blueprint $table) {
            $table->id();
            $table->char('code', 1)->unique();
            $table->string('label', 45)->unique();
            $table->string('sex', 10);
            $table->string('image_path');
            $table->timestamps();
        });

        Schema::create('hairstyles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('body_type_id')->constrained()->restrictOnDelete();
            $table->string('name', 45);
            $table->string('image_path');
            $table->timestamps();

            $table->unique(['body_type_id', 'name']);
        });

        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45)->unique();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('diplomas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('short_description');
            $table->text('long_description')->nullable();
            $table->decimal('salary', 10, 2)->unsigned();
            $table->string('image_path')->nullable();
            $table->foreignId('required_diploma_id')->nullable()->constrained('diplomas')->restrictOnDelete();
            $table->foreignId('place_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('job_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description');
            $table->decimal('amount', 10, 2)->default(0);
            $table->unsignedTinyInteger('success_chance');
            $table->timestamps();

            $table->unique(['job_id', 'name']);
        });

        Schema::create('studies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('short_description');
            $table->text('long_description')->nullable();
            $table->decimal('price', 10, 2)->unsigned();
            $table->unsignedInteger('duration_days');
            $table->string('image_path')->nullable();
            $table->foreignId('awarded_diploma_id')->constrained('diplomas')->restrictOnDelete();
            $table->foreignId('required_diploma_id')->nullable()->constrained('diplomas')->restrictOnDelete();
            $table->foreignId('place_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45)->unique();
            $table->char('hex_code', 7)->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->decimal('price', 10, 2)->unsigned();
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('category', 45);
            $table->timestamps();
        });

        Schema::create('item_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('gauge', 30);
            $table->smallInteger('effect');
            $table->timestamps();

            $table->unique(['item_id', 'gauge']);
        });

        Schema::create('wearables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('body_type_id')->constrained()->restrictOnDelete();
            $table->string('category', 20);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->unsigned();
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->unique(['body_type_id', 'category', 'name']);
        });

        Schema::create('animal_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sicknesses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->json('chance_by_age')->nullable();
            $table->string('type', 20);
            $table->boolean('needs_doctor')->default(false);
            $table->boolean('self_resolving')->default(true);
            $table->decimal('treatment_cost', 10, 2)->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('sickness_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sickness_id')->constrained()->cascadeOnDelete();
            $table->string('gauge', 30);
            $table->string('operator', 2)->default('<=');
            $table->smallInteger('threshold');
            $table->timestamps();

            $table->unique(['sickness_id', 'gauge', 'operator', 'threshold'], 'sickness_condition_unique');
        });

        Schema::create('sickness_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sickness_id')->constrained()->cascadeOnDelete();
            $table->string('gauge', 30);
            $table->smallInteger('effect');
            $table->timestamps();

            $table->unique(['sickness_id', 'gauge']);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description');
            $table->decimal('price', 10, 2)->unsigned();
            $table->string('category', 45);
            $table->timestamps();
        });

        Schema::create('activity_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('gauge', 30);
            $table->smallInteger('effect');
            $table->timestamps();

            $table->unique(['activity_id', 'gauge']);
        });

        Schema::create('sport_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('type', 45)->default('gym');
            $table->decimal('price', 10, 2)->unsigned();
            $table->unsignedInteger('duration_days');
            $table->unsignedTinyInteger('physical_condition_effect');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rewinds', function (Blueprint $table) {
            $table->id();
            $table->decimal('price', 10, 2)->unsigned();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE body_types ADD CONSTRAINT body_types_sex_check CHECK (sex IN ('male', 'female'))");
        DB::statement('ALTER TABLE job_actions ADD CONSTRAINT job_actions_chance_check CHECK (success_chance <= 100)');
        DB::statement('ALTER TABLE studies ADD CONSTRAINT studies_duration_check CHECK (duration_days > 0)');
        DB::statement("ALTER TABLE wearables ADD CONSTRAINT wearables_category_check CHECK (category IN ('top', 'bottom', 'shoes'))");

        foreach (['item_effects', 'sickness_conditions', 'sickness_effects', 'activity_effects'] as $table) {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_gauge_check CHECK (gauge IN ('hunger', 'thirst', 'clean', 'happiness', 'entertainment', 'physical_condition', 'health'))");
        }

        DB::statement("ALTER TABLE sickness_conditions ADD CONSTRAINT sickness_conditions_operator_check CHECK (operator IN ('<', '<=', '=', '>=', '>'))");
        DB::statement("ALTER TABLE sicknesses ADD CONSTRAINT sicknesses_type_check CHECK (type IN ('random', 'negligence', 'severe'))");
        DB::statement('ALTER TABLE sport_sessions ADD CONSTRAINT sport_sessions_duration_check CHECK (duration_days > 0)');
        DB::statement('ALTER TABLE sport_sessions ADD CONSTRAINT sport_sessions_effect_check CHECK (physical_condition_effect <= 100)');
        DB::statement('ALTER TABLE events ADD CONSTRAINT events_dates_check CHECK (ends_at IS NULL OR ends_at >= starts_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('rewinds');
        Schema::dropIfExists('events');
        Schema::dropIfExists('sport_sessions');
        Schema::dropIfExists('activity_effects');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('sickness_effects');
        Schema::dropIfExists('sickness_conditions');
        Schema::dropIfExists('sicknesses');
        Schema::dropIfExists('animal_types');
        Schema::dropIfExists('wearables');
        Schema::dropIfExists('item_effects');
        Schema::dropIfExists('items');
        Schema::dropIfExists('colors');
        Schema::dropIfExists('studies');
        Schema::dropIfExists('job_actions');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('diplomas');
        Schema::dropIfExists('places');
        Schema::dropIfExists('hairstyles');
        Schema::dropIfExists('body_types');
    }
};

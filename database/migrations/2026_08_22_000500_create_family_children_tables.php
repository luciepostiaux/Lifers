<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('given_names', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('sex', 10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['name', 'sex']);
            $table->index(['sex', 'is_active']);
        });

        Schema::create('family_pregnancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intimacy_event_id')->nullable()->unique()->constrained('lifer_intimacy_events')->nullOnDelete();
            $table->foreignId('mother_lifer_id')->nullable()->constrained('lifers')->nullOnDelete();
            $table->foreignId('father_lifer_id')->nullable()->constrained('lifers')->nullOnDelete();
            $table->unsignedTinyInteger('children_count');
            $table->string('status', 15)->default('active');
            $table->dateTime('conceived_at');
            $table->dateTime('due_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['mother_lifer_id', 'status', 'due_at'], 'family_pregnancies_mother_status_index');
            $table->index(['father_lifer_id', 'status']);
        });

        Schema::create('family_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->nullable()->constrained('family_pregnancies')->nullOnDelete();
            $table->foreignId('biological_mother_lifer_id')->nullable()->constrained('lifers')->nullOnDelete();
            $table->foreignId('biological_father_lifer_id')->nullable()->constrained('lifers')->nullOnDelete();
            $table->foreignId('claimed_lifer_id')->nullable()->unique()->constrained('lifers')->nullOnDelete();
            $table->unsignedTinyInteger('birth_order')->default(1);
            $table->string('first_name', 45)->nullable();
            $table->string('last_name', 45)->nullable();
            $table->string('sex', 10);
            $table->string('status', 15)->default('expected');
            $table->dateTime('conceived_at');
            $table->dateTime('born_at')->nullable();
            $table->dateTime('adult_at')->nullable();
            $table->dateTime('died_at')->nullable();
            $table->string('death_cause')->nullable();
            $table->timestamps();

            $table->unique(['pregnancy_id', 'birth_order']);
            $table->index(['status', 'adult_at']);
            $table->index(['biological_mother_lifer_id', 'status'], 'family_children_mother_status_index');
            $table->index(['biological_father_lifer_id', 'status'], 'family_children_father_status_index');
        });

        Schema::create('family_child_gauges', function (Blueprint $table) {
            $table->foreignId('child_id')->primary()->constrained('family_children')->cascadeOnDelete();
            $table->unsignedTinyInteger('hunger')->default(100);
            $table->unsignedTinyInteger('hygiene')->default(100);
            $table->unsignedTinyInteger('affection')->default(100);
            $table->date('red_since')->nullable();
            $table->date('last_decreased_on')->nullable();
            $table->timestamps();
        });

        Schema::create('family_child_guardians', function (Blueprint $table) {
            $table->foreignId('child_id')->constrained('family_children')->cascadeOnDelete();
            $table->foreignId('lifer_id')->constrained('lifers')->cascadeOnDelete();
            $table->string('type', 15);
            $table->boolean('has_custody')->default(true);
            $table->dateTime('adopted_at')->nullable();
            $table->dateTime('renounced_at')->nullable();
            $table->timestamps();

            $table->primary(['child_id', 'lifer_id']);
            $table->index(['lifer_id', 'has_custody']);
            $table->index(['lifer_id', 'adopted_at']);
        });

        Schema::table('family_requests', function (Blueprint $table) {
            $table->foreignId('child_id')->nullable()->after('recipient_lifer_id')->constrained('family_children')->cascadeOnDelete();
        });

        DB::statement("ALTER TABLE given_names ADD CONSTRAINT given_names_sex_check CHECK (sex IN ('male', 'female'))");
        DB::statement('ALTER TABLE family_pregnancies ADD CONSTRAINT family_pregnancies_children_count_check CHECK (children_count BETWEEN 1 AND 3)');
        DB::statement("ALTER TABLE family_pregnancies ADD CONSTRAINT family_pregnancies_status_check CHECK (status IN ('active', 'completed', 'lost'))");
        DB::statement('ALTER TABLE family_pregnancies ADD CONSTRAINT family_pregnancies_dates_check CHECK (due_at >= conceived_at AND (completed_at IS NULL OR completed_at >= conceived_at))');
        DB::statement("ALTER TABLE family_children ADD CONSTRAINT family_children_sex_check CHECK (sex IN ('male', 'female'))");
        DB::statement("ALTER TABLE family_children ADD CONSTRAINT family_children_status_check CHECK (status IN ('expected', 'dependent', 'orphaned', 'available', 'claimed', 'dead'))");
        DB::statement('ALTER TABLE family_children ADD CONSTRAINT family_children_birth_order_check CHECK (birth_order BETWEEN 1 AND 3)');
        DB::statement('ALTER TABLE family_child_gauges ADD CONSTRAINT family_child_gauges_range_check CHECK (hunger <= 100 AND hygiene <= 100 AND affection <= 100)');
        DB::statement("ALTER TABLE family_child_guardians ADD CONSTRAINT family_child_guardians_type_check CHECK (type IN ('biological', 'adoptive'))");
    }

    public function down(): void
    {
        Schema::table('family_requests', function (Blueprint $table) {
            $table->dropForeign(['child_id']);
            $table->dropColumn('child_id');
        });

        Schema::dropIfExists('family_child_guardians');
        Schema::dropIfExists('family_child_gauges');
        Schema::dropIfExists('family_children');
        Schema::dropIfExists('family_pregnancies');
        Schema::dropIfExists('given_names');
    }
};

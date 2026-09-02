<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lifer_profiles', function (Blueprint $table) {
            $table->foreignId('lifer_id')->primary()->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->json('content')->nullable();
            $table->boolean('show_money')->default(false);
            $table->timestamps();
        });

        Schema::table('lifer_diplomas', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('earned_at');
        });

        DB::table('profile_comments')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('lifer_game_states')
                    ->whereColumn('lifer_game_states.lifer_id', 'profile_comments.author_lifer_id');
            })
            ->orWhereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('lifer_game_states')
                    ->whereColumn('lifer_game_states.lifer_id', 'profile_comments.receiver_lifer_id');
            })
            ->delete();

        Schema::table('profile_comments', function (Blueprint $table) {
            $table->dropForeign(['author_lifer_id']);
            $table->dropForeign(['receiver_lifer_id']);
            $table->string('status', 15)->default('pending')->after('content');
            $table->dateTime('moderated_at')->nullable()->after('status');
            $table->index(['receiver_lifer_id', 'status', 'created_at'], 'profile_comments_moderation_index');

            $table->foreign('author_lifer_id')->references('lifer_id')->on('lifer_game_states')->cascadeOnDelete();
            $table->foreign('receiver_lifer_id')->references('lifer_id')->on('lifer_game_states')->cascadeOnDelete();
        });

        DB::statement("ALTER TABLE profile_comments ADD CONSTRAINT profile_comments_status_check CHECK (status IN ('pending', 'approved'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE profile_comments DROP CHECK profile_comments_status_check');

        Schema::table('profile_comments', function (Blueprint $table) {
            $table->dropForeign(['author_lifer_id']);
            $table->dropForeign(['receiver_lifer_id']);
            $table->dropIndex('profile_comments_moderation_index');
            $table->dropColumn(['status', 'moderated_at']);

            $table->foreign('author_lifer_id')->references('id')->on('lifers')->cascadeOnDelete();
            $table->foreign('receiver_lifer_id')->references('id')->on('lifers')->cascadeOnDelete();
        });

        Schema::table('lifer_diplomas', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });

        Schema::dropIfExists('lifer_profiles');
    }
};

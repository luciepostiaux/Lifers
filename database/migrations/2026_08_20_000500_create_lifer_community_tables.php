<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('type', 20);
            $table->string('key')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('conversation_lifer', function (Blueprint $table) {
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['conversation_id', 'lifer_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_lifer_id')->constrained('lifers')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        Schema::create('message_reads', function (Blueprint $table) {
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reader_lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->dateTime('read_at');

            $table->primary(['message_id', 'reader_lifer_id']);
        });

        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('recipient_lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->string('status', 15)->default('pending');
            $table->unsignedBigInteger('lower_lifer_id');
            $table->unsignedBigInteger('higher_lifer_id');
            $table->timestamps();

            $table->unique(['lower_lifer_id', 'higher_lifer_id']);
        });

        Schema::create('event_lifer', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['event_id', 'lifer_id']);
        });

        Schema::create('lifer_rewind', function (Blueprint $table) {
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->foreignId('rewind_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['lifer_id', 'rewind_id']);
        });

        Schema::create('profile_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_lifer_id')->constrained('lifers')->cascadeOnDelete();
            $table->foreignId('receiver_lifer_id')->constrained('lifers')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();

            $table->index(['receiver_lifer_id', 'created_at']);
        });

        Schema::create('lifer_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->string('image_path');
            $table->timestamps();
        });

        Schema::create('comment_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('profile_comments')->cascadeOnDelete();
            $table->foreignId('owner_lifer_id')->constrained('lifer_game_states', 'lifer_id')->cascadeOnDelete();
            $table->string('image_path');
            $table->timestamps();
        });

        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lifer_id')->constrained('lifers')->cascadeOnDelete();
            $table->text('content');
            $table->string('status', 15)->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE conversations ADD CONSTRAINT conversations_type_check CHECK (type IN ('general', 'private'))");
        DB::statement("ALTER TABLE friendships ADD CONSTRAINT friendships_status_check CHECK (status IN ('pending', 'accepted'))");
        DB::statement('ALTER TABLE friendships ADD CONSTRAINT friendships_distinct_lifers_check CHECK (requester_lifer_id <> recipient_lifer_id)');
        DB::statement('ALTER TABLE friendships ADD CONSTRAINT friendships_canonical_pair_check CHECK (lower_lifer_id = LEAST(requester_lifer_id, recipient_lifer_id) AND higher_lifer_id = GREATEST(requester_lifer_id, recipient_lifer_id))');
        DB::statement("ALTER TABLE suggestions ADD CONSTRAINT suggestions_status_check CHECK (status IN ('pending', 'accepted', 'rejected'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestions');
        Schema::dropIfExists('comment_images');
        Schema::dropIfExists('lifer_images');
        Schema::dropIfExists('profile_comments');
        Schema::dropIfExists('lifer_rewind');
        Schema::dropIfExists('event_lifer');
        Schema::dropIfExists('friendships');
        Schema::dropIfExists('message_reads');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_lifer');
        Schema::dropIfExists('conversations');
    }
};

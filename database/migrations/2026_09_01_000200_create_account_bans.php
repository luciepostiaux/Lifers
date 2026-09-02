<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->unique();
            $table->text('reason');
            $table->foreignId('banned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('banned_at');
            $table->dateTime('revoked_at')->nullable();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('revocation_reason')->nullable();
            $table->timestamps();

            $table->index(['revoked_at', 'banned_at']);
        });

        Schema::create('account_ban_ip_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_ban_id')->constrained()->cascadeOnDelete();
            $table->char('ip_hash', 64)->unique();
            $table->string('masked_ip', 45);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_ban_ip_addresses');
        Schema::dropIfExists('account_bans');
    }
};

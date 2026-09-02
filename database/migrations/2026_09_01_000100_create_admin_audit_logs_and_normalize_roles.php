<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80);
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
        });

        foreach ([Role::USER, Role::MODERATOR, Role::ADMIN] as $roleName) {
            DB::table('roles')->updateOrInsert(
                ['name' => $roleName],
                ['created_at' => now(), 'updated_at' => now()],
            );
        }

        $roles = DB::table('roles')->whereIn('name', [
            Role::USER,
            Role::ADMIN,
        ])->pluck('id', 'name');

        DB::table('users')
            ->orderBy('id')
            ->get(['id', 'email'])
            ->each(function (object $user) use ($roles): void {
                $hasRole = DB::table('role_user')->where('user_id', $user->id)->exists();

                if (! $hasRole) {
                    DB::table('role_user')->insert([
                        'user_id' => $user->id,
                        'role_id' => $roles[Role::USER],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if (mb_strtolower(trim($user->email)) !== User::TRUSTED_ADMIN_EMAIL) {
                    return;
                }

                DB::table('role_user')->where('user_id', $user->id)->delete();
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $roles[Role::ADMIN],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');

        $moderatorRoleId = DB::table('roles')->where('name', Role::MODERATOR)->value('id');

        if ($moderatorRoleId) {
            DB::table('role_user')->where('role_id', $moderatorRoleId)->delete();
            DB::table('roles')->where('id', $moderatorRoleId)->delete();
        }
    }
};

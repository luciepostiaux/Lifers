<?php

namespace Tests\Feature;

use App\Models\AccountBan;
use App\Models\AdminAuditLog;
use App\Models\Role;
use App\Models\Sickness;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class AdminControlTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_admin_can_open_complete_active_lifer_controls(): void
    {
        $admin = $this->createAdmin();
        [, $lifer] = $this->createUserWithLifer();

        $this->actingAs($admin)
            ->get(route('admin.lifers.show', $lifer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Lifer')
                ->where('lifer.id', $lifer->id)
                ->where('lifer.money', '900.00')
                ->has('lifer.gauges', 7)
                ->has('sicknessCatalog')
                ->has('diplomaCatalog'));
    }

    public function test_admin_can_adjust_money_and_gauges_with_an_audit_trail(): void
    {
        $admin = $this->createAdmin();
        [, $lifer] = $this->createUserWithLifer(900);

        $this->actingAs($admin)
            ->patch(route('admin.lifers.money.update', $lifer), [
                'amount' => 125.50,
                'reason' => 'Correction après un incident de paiement.',
            ])
            ->assertSessionHas('success');

        $this->assertSame('1025.50', $lifer->gameState()->value('money'));
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $lifer->user_id,
            'action' => 'lifer.money.updated',
        ]);

        $gauges = [
            'hunger' => 10,
            'thirst' => 20,
            'clean' => 30,
            'happiness' => 40,
            'entertainment' => 50,
            'physical_condition' => 60,
            'health' => 70,
        ];

        $this->actingAs($admin)
            ->patch(route('admin.lifers.gauges.update', $lifer), [
                'gauges' => $gauges,
                'reason' => 'Restauration manuelle de l’état de jeu.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('life_gauges', ['lifer_id' => $lifer->id, ...$gauges]);
        $this->assertDatabaseHas('admin_audit_logs', [
            'action' => 'lifer.gauges.updated',
            'target_user_id' => $lifer->user_id,
        ]);
    }

    public function test_admin_debit_never_creates_a_negative_balance(): void
    {
        $admin = $this->createAdmin();
        [, $lifer] = $this->createUserWithLifer(40);

        $this->actingAs($admin)
            ->patch(route('admin.lifers.money.update', $lifer), [
                'amount' => -100,
                'reason' => 'Retrait administratif contrôlé.',
            ])
            ->assertSessionHas('success');

        $this->assertSame('0.00', $lifer->gameState()->value('money'));
    }

    public function test_admin_can_add_and_remove_a_sickness(): void
    {
        $admin = $this->createAdmin();
        [, $lifer] = $this->createUserWithLifer(gauges: ['health' => 100]);
        $sickness = Sickness::query()->create([
            'name' => 'Maladie administrative',
            'slug' => 'maladie-administrative',
            'description' => 'Maladie utilisée pour vérifier le panneau.',
            'duration_days' => 2,
            'type' => 'random',
            'self_resolving' => true,
        ]);
        $sickness->effects()->create(['gauge' => 'health', 'effect' => -12]);

        $this->actingAs($admin)
            ->post(route('admin.lifers.sicknesses.store', $lifer), [
                'sickness_id' => $sickness->id,
                'reason' => 'Test du contrôle sanitaire.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('lifer_sicknesses', [
            'lifer_id' => $lifer->id,
            'sickness_id' => $sickness->id,
        ]);
        $this->assertSame(88, $lifer->lifeGauge()->value('health'));

        $this->actingAs($admin)
            ->delete(route('admin.lifers.sicknesses.destroy', [$lifer, $sickness]), [
                'reason' => 'Fin du test du contrôle sanitaire.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('lifer_sicknesses', [
            'lifer_id' => $lifer->id,
            'sickness_id' => $sickness->id,
        ]);
    }

    public function test_admin_kill_uses_normal_death_and_preserves_identity(): void
    {
        $admin = $this->createAdmin();
        [, $lifer] = $this->createUserWithLifer(firstName: 'Cible', lastName: 'Administrative');

        $this->actingAs($admin)
            ->post(route('admin.lifers.kill', $lifer), [
                'cause' => 'Décès décidé par l’administration',
                'reason' => 'Sanction exceptionnelle documentée.',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $lifer->refresh();
        $this->assertSame('dead', $lifer->status);
        $this->assertSame('Cible', $lifer->first_name);
        $this->assertSame('Administrative', $lifer->last_name);
        $this->assertSame('Décès décidé par l’administration', $lifer->death_cause);
        $this->assertNull($lifer->gameState);
        $this->assertDatabaseHas('admin_audit_logs', [
            'action' => 'lifer.killed',
            'target_user_id' => $lifer->user_id,
        ]);
    }

    public function test_admin_can_ban_an_account_and_its_known_ip_addresses(): void
    {
        $admin = $this->createAdmin();
        $target = User::factory()->create(['email' => 'problem@example.com']);
        DB::table('sessions')->insert([
            'id' => 'target-session',
            'user_id' => $target->id,
            'ip_address' => '203.0.113.42',
            'user_agent' => 'Test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bans.store'), [
                'email' => 'PROBLEM@example.com',
                'reason' => 'Harcèlement répété dans la communauté.',
                'block_known_ip_addresses' => true,
            ])
            ->assertSessionHas('success');

        $ban = AccountBan::query()->where('email', 'problem@example.com')->firstOrFail();
        $this->assertTrue($target->isBanned());
        $this->assertDatabaseMissing('sessions', ['id' => 'target-session']);
        $this->assertSame(['203.0.x.x'], $ban->ipAddresses()->pluck('masked_ip')->all());
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $target->id,
            'action' => 'account.banned',
        ]);

        $this->app['auth']->forgetGuards();
        $this->app['auth']->shouldUse('web');
        $this->post('/login', [
            'email' => 'problem@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_banned_email_and_ip_cannot_create_another_account(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.bans.store'), [
            'email' => 'blocked@example.com',
            'reason' => 'Adresse interdite avant inscription.',
        ]);

        $this->app['auth']->forgetGuards();
        $this->app['auth']->shouldUse('web');

        $this->post('/register', [
            'name' => 'Blocked',
            'email' => 'BLOCKED@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => false,
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);

        $target = User::factory()->create(['email' => 'known-ip@example.com']);
        DB::table('sessions')->insert([
            'id' => 'known-ip-session',
            'user_id' => $target->id,
            'ip_address' => '198.51.100.24',
            'user_agent' => 'Test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)->post(route('admin.bans.store'), [
            'email' => $target->email,
            'reason' => 'Compte et adresse réseau interdits.',
            'block_known_ip_addresses' => true,
        ]);

        $this->app['auth']->forgetGuards();
        $this->app['auth']->shouldUse('web');

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.24'])
            ->post('/register', [
                'name' => 'Nouvelle tentative',
                'email' => 'another-address@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'terms' => false,
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'another-address@example.com']);
    }

    public function test_admin_can_lift_a_ban_but_cannot_ban_primary_admin(): void
    {
        $admin = User::factory()->create(['email' => User::TRUSTED_ADMIN_EMAIL]);

        $this->actingAs($admin)
            ->post(route('admin.bans.store'), [
                'email' => User::TRUSTED_ADMIN_EMAIL,
                'reason' => 'Action qui doit être refusée.',
            ])
            ->assertSessionHasErrors('email');

        $this->actingAs($admin)->post(route('admin.bans.store'), [
            'email' => 'temporary@example.com',
            'reason' => 'Bannissement temporaire de vérification.',
        ]);
        $ban = AccountBan::query()->where('email', 'temporary@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.bans.destroy', $ban), [
                'reason' => 'Erreur de modération corrigée.',
            ])
            ->assertSessionHas('success');

        $this->assertNotNull($ban->fresh()->revoked_at);
        $this->assertFalse($ban->fresh()->ipAddresses()->exists());
        $this->assertDatabaseHas('admin_audit_logs', [
            'action' => 'account.unbanned',
        ]);
    }

    public function test_banned_authenticated_account_is_logged_out(): void
    {
        $admin = $this->createAdmin();
        $target = User::factory()->create(['email' => 'banned-session@example.com']);

        $this->actingAs($admin)->post(route('admin.bans.store'), [
            'email' => $target->email,
            'reason' => 'Bannissement avec session encore simulée.',
        ]);

        $this->actingAs($target)
            ->get('/dashboard')
            ->assertForbidden();

        $this->assertGuest();
    }

    public function test_non_admin_cannot_use_sensitive_control_routes(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();

        $this->actingAs($user)
            ->patch(route('admin.lifers.money.update', $lifer), [
                'amount' => 100,
                'reason' => 'Tentative non autorisée.',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.bans.store'), [
                'email' => 'victim@example.com',
                'reason' => 'Tentative non autorisée.',
            ])
            ->assertForbidden();

        $this->assertSame(0, AdminAuditLog::query()->count());
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $role = Role::query()->firstOrCreate(['name' => Role::ADMIN]);
        $admin->roles()->attach($role->id);

        return $admin;
    }
}

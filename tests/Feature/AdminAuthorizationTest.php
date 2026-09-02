<?php

namespace Tests\Feature;

use App\Models\Diploma;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_a_non_admin_cannot_open_the_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_a_moderator_cannot_open_the_admin_dashboard(): void
    {
        $moderator = User::factory()->create();
        $role = Role::query()->firstOrCreate(['name' => Role::MODERATOR]);
        $moderator->roles()->attach($role->id);

        $this->actingAs($moderator)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_an_admin_can_open_the_admin_dashboard(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->where('stats.users', 1)
                ->where('permissions.admin', true)
                ->has('users.data', 1)
                ->missing('lifers')
                ->missing('diplomas')
                ->has('auditLogs'));
    }

    public function test_the_reserved_admin_account_is_always_an_admin_without_a_pivot_role(): void
    {
        $admin = User::factory()->unverified()->create([
            'email' => User::TRUSTED_ADMIN_EMAIL,
        ]);

        $this->assertFalse($admin->roles()->exists());

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_admin_can_promote_and_demote_a_moderator_with_an_audit_log(): void
    {
        $admin = $this->createAdmin();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.role.update', $target), [
                'role' => Role::MODERATOR,
            ])
            ->assertSessionHas('success');

        $this->assertTrue($target->fresh()->hasRole(Role::MODERATOR));
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $target->id,
            'action' => 'role.updated',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.role.update', $target), [
                'role' => Role::USER,
            ])
            ->assertSessionHas('success');

        $target->refresh();
        $this->assertTrue($target->hasRole(Role::USER));
        $this->assertFalse($target->hasRole(Role::MODERATOR));
    }

    public function test_admin_role_cannot_be_assigned_and_reserved_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->create(['email' => User::TRUSTED_ADMIN_EMAIL]);
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.role.update', $target), [
                'role' => Role::ADMIN,
            ])
            ->assertSessionHasErrors('role');

        $this->actingAs($admin)
            ->patch(route('admin.users.role.update', $admin), [
                'role' => Role::USER,
            ])
            ->assertSessionHasErrors('role');

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_an_admin_can_grant_and_remove_a_diploma(): void
    {
        $admin = $this->createAdmin();
        [, $lifer] = $this->createUserWithLifer();
        $diploma = Diploma::create([
            'name' => 'Diplôme de test',
            'description' => 'Diplôme utilisé par le test administrateur.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.grantDiploma'), [
                'liferId' => $lifer->id,
                'diplomaId' => $diploma->id,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('lifer_diplomas', [
            'lifer_id' => $lifer->id,
            'diploma_id' => $diploma->id,
        ]);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $lifer->user_id,
            'action' => 'diploma.granted',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.removeDiploma'), [
                'liferId' => $lifer->id,
                'diplomaId' => $diploma->id,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('lifer_diplomas', [
            'lifer_id' => $lifer->id,
            'diploma_id' => $diploma->id,
        ]);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $lifer->user_id,
            'action' => 'diploma.removed',
        ]);
    }

    public function test_admin_diploma_actions_reject_a_missing_active_lifer(): void
    {
        $admin = $this->createAdmin();
        $userWithoutCharacter = User::factory()->create();
        $diploma = Diploma::create([
            'name' => 'Diplôme de test',
            'description' => 'Diplôme utilisé par le test administrateur.',
        ]);

        foreach (['admin.grantDiploma', 'admin.removeDiploma'] as $routeName) {
            $this->actingAs($admin)
                ->post(route($routeName), [
                    'liferId' => $userWithoutCharacter->id,
                    'diplomaId' => $diploma->id,
                ])
                ->assertSessionHasErrors('liferId');
        }
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $role = Role::query()->firstOrCreate(['name' => Role::ADMIN]);
        $admin->roles()->attach($role->id);

        return $admin;
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->put('/user/profile-information', [
            'name' => 'Test Name',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Test Name', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }

    public function test_primary_admin_email_cannot_be_changed(): void
    {
        $admin = User::factory()->create(['email' => User::TRUSTED_ADMIN_EMAIL]);

        $this->actingAs($admin)
            ->put('/user/profile-information', [
                'name' => 'Administration Lifers',
                'email' => 'different@example.com',
            ])
            ->assertSessionHasErrors('email', errorBag: 'updateProfileInformation');

        $this->assertSame(User::TRUSTED_ADMIN_EMAIL, $admin->fresh()->email);
        $this->assertTrue($admin->fresh()->isAdmin());
    }
}

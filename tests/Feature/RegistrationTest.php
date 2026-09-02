<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    {
        if (Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_new_users_can_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'adult_confirmation' => true,
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
        $user = User::where('email', 'test@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertTrue($user->hasRole(Role::USER));
        $this->assertNotNull($user->adult_confirmed_at);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertSame(config('legal.terms_version'), $user->terms_version);
        $this->assertNotNull($user->privacy_acknowledged_at);
        $this->assertSame(config('legal.privacy_version'), $user->privacy_version);
    }

    public function test_registration_rejects_invalid_duplicate_and_reserved_email_addresses(): void
    {
        User::factory()->create(['email' => 'unique@example.com']);

        foreach (['adresse-invalide', 'UNIQUE@example.com', User::TRUSTED_ADMIN_EMAIL] as $email) {
            $this->post('/register', [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
                'adult_confirmation' => true,
                'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
            ])->assertSessionHasErrors('email');
        }

        $this->assertSame(1, User::count());
    }

    public function test_registration_requires_adult_confirmation(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'adult@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ])->assertSessionHasErrors('adult_confirmation');

        $this->assertDatabaseMissing('users', ['email' => 'adult@example.com']);
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'terms@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'adult_confirmation' => true,
            'terms' => false,
        ])->assertSessionHasErrors('terms');

        $this->assertDatabaseMissing('users', ['email' => 'terms@example.com']);
    }

    public function test_trusted_admin_email_is_considered_verified_without_timestamp(): void
    {
        $admin = User::factory()->unverified()->create(['email' => User::TRUSTED_ADMIN_EMAIL]);

        $this->assertTrue($admin->hasVerifiedEmail());
    }
}

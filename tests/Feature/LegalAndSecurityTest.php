<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LegalAndSecurityTest extends TestCase
{
    public function test_terms_and_privacy_pages_are_available_in_french(): void
    {
        $this->get('/terms-of-service')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('TermsOfService')
                ->where('terms', fn (string $terms): bool => str_contains($terms, 'Conditions d’utilisation de Lifers')
                    && str_contains($terms, 'Version du 2 septembre 2026')));

        $this->get('/privacy-policy')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('PrivacyPolicy')
                ->where('policy', fn (string $policy): bool => str_contains($policy, 'Politique de confidentialité de Lifers')
                    && str_contains($policy, 'privacy@luciepostiaux.com')));
    }

    public function test_public_responses_include_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');
    }
}

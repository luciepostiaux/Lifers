<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedHostingCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pusher_public_configuration_is_rendered_without_exposing_the_secret(): void
    {
        config()->set('broadcasting.default', 'pusher');
        config()->set('broadcasting.connections.pusher.key', 'public-pusher-key');
        config()->set('broadcasting.connections.pusher.secret', 'server-only-secret');
        config()->set('broadcasting.connections.pusher.options.cluster', 'eu');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="pusher-key" content="public-pusher-key">', false)
            ->assertSee('<meta name="pusher-cluster" content="eu">', false)
            ->assertDontSee('server-only-secret', false);
    }

    public function test_pusher_metadata_is_omitted_when_the_public_key_is_missing(): void
    {
        config()->set('broadcasting.default', 'pusher');
        config()->set('broadcasting.connections.pusher.key', null);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('name="pusher-key"', false)
            ->assertDontSee('name="pusher-cluster"', false);
    }

    public function test_shared_hosting_tick_can_run_safely_on_an_empty_database(): void
    {
        $this->artisan('lifers:shared-hosting-tick')
            ->expectsOutputToContain('Les cycles compatibles avec l’hébergement mutualisé ont été exécutés.')
            ->assertSuccessful();

        $this->assertFileExists(base_path('cron/lifers-shared-hosting.php'));
        $this->assertStringNotContainsString(
            public_path(),
            realpath(base_path('cron/lifers-shared-hosting.php')),
        );
    }
}

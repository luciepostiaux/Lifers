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

    public function test_ovh_runtime_uses_the_supported_production_php_environment(): void
    {
        $configuration = file_get_contents(base_path('.ovhconfig'));

        $this->assertStringContainsString('app.engine=php', $configuration);
        $this->assertStringContainsString('app.engine.version=8.5', $configuration);
        $this->assertStringContainsString('environment=production', $configuration);
        $this->assertStringContainsString('container.image=stable64', $configuration);
    }

    public function test_production_environment_template_has_secure_defaults_without_secrets(): void
    {
        $template = file_get_contents(base_path('.env.production.example'));

        $this->assertStringContainsString('APP_ENV=production', $template);
        $this->assertStringContainsString('APP_DEBUG=false', $template);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $template);
        $this->assertStringContainsString('LIFERS_HOSTING_BOOTSTRAP_ENABLED=false', $template);
        $this->assertMatchesRegularExpression('/^APP_KEY=$/m', $template);
        $this->assertMatchesRegularExpression('/^DB_PASSWORD=$/m', $template);
        $this->assertMatchesRegularExpression('/^MAIL_PASSWORD=$/m', $template);
        $this->assertMatchesRegularExpression('/^PUSHER_APP_SECRET=$/m', $template);
    }

    public function test_one_time_bootstrap_is_cli_only_outside_public_and_disabled_by_default(): void
    {
        $path = base_path('cron/lifers-bootstrap.php');
        $launcher = file_get_contents($path);

        $this->assertFileExists($path);
        $this->assertStringNotContainsString(public_path(), realpath($path));
        $this->assertStringContainsString("PHP_SAPI !== 'cli'", $launcher);
        $this->assertStringContainsString("environment('production')", $launcher);
        $this->assertStringContainsString("config('hosting.bootstrap_enabled')", $launcher);
        $this->assertFalse(config('hosting.bootstrap_enabled'));
    }

    public function test_deployment_package_builder_excludes_private_and_development_files(): void
    {
        $script = file_get_contents(base_path('scripts/build-ovh-package.sh'));

        foreach ([
            '.env',
            '.env.example',
            'AGENTS.md',
            'README.md',
            'database/factories/',
            'database/seeders/DemoSeeder.php',
            'docs/',
            'node_modules/',
            'package.json',
            'phpunit.xml',
            'resources/css/',
            'resources/js/',
            'tests/',
            'tmp/',
            'vendor/',
        ] as $excluded) {
            $this->assertStringContainsString("--exclude='{$excluded}'", $script);
        }

        $this->assertStringContainsString('--no-dev', $script);
        $this->assertStringContainsString('--optimize-autoloader', $script);
    }
}

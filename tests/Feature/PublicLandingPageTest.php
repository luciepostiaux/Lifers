<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicLandingPageTest extends TestCase
{
    public function test_public_home_exposes_final_seo_metadata_in_the_initial_response(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('seo.title', 'Lifers — Ta seconde vie commence ici')
                ->where('seo.canonicalUrl', url('/'))
                ->where('seo.socialImageUrl', url('/images/landing/hero-lifers.png'))
                ->where('seo.socialImageAlt', 'Deux Lifers dans une ville illustrée et chaleureuse')
                ->where('seo.locale', 'fr_BE')
                ->where('seo.alternateLocale', 'fr_FR')
                ->where('seo.author', 'Lucie Postiaux')
                ->where('seo.structuredData.@type', 'WebSite')
                ->where('seo.structuredData.publisher.@type', 'Person')
                ->where('seo.description', fn (string $description) => str_contains($description, 'jeu de simulation de vie communautaire'))
                ->where('canLogin', true)
                ->where('canRegister', true));

        $response
            ->assertSee('<html lang="fr">', false)
            ->assertSee('<title data-inertia>Lifers — Ta seconde vie commence ici</title>', false)
            ->assertSee('<link rel="icon" href="/favicon.svg" type="image/svg+xml">', false)
            ->assertSee('<link rel="apple-touch-icon" href="/apple-touch-icon.png">', false)
            ->assertSee('<link rel="manifest" href="/site.webmanifest">', false)
            ->assertSee('data-inertia="description" name="description"', false)
            ->assertSee('data-inertia="robots" name="robots" content="index, follow, max-image-preview:large"', false)
            ->assertSee('data-inertia="canonical" rel="canonical" href="'.url('/').'"', false)
            ->assertSee('rel="alternate" hreflang="fr" href="'.url('/').'"', false)
            ->assertSee('name="author" content="Lucie Postiaux"', false)
            ->assertSee('property="og:image" content="'.url('/images/landing/hero-lifers.png').'"', false)
            ->assertSee('property="og:image:type" content="image/png"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('name="twitter:image:alt" content="Deux Lifers dans une ville illustrée et chaleureuse"', false)
            ->assertSee('type="application/ld+json"', false)
            ->assertSee('"@type":"WebSite"', false);
    }

    public function test_private_and_authentication_pages_are_not_indexable(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_sitemap_contains_only_the_public_home_page(): void
    {
        $response = $this->get(route('sitemap'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
            ->assertSee('<loc>'.url('/').'</loc>', false);

        $this->assertSame(1, substr_count($response->getContent(), '<url>'));
        $this->assertStringNotContainsString('/dashboard', $response->getContent());
        $this->assertStringNotContainsString('/lifers/', $response->getContent());
    }

    public function test_robots_file_points_to_the_current_host_and_excludes_private_areas(): void
    {
        $response = $this->get(route('robots'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSeeText('User-agent: *')
            ->assertSeeText('Allow: /')
            ->assertSeeText('Disallow: /dashboard')
            ->assertSeeText('Disallow: /admin')
            ->assertSeeText('Disallow: /lifers')
            ->assertSeeText('Sitemap: '.url('/sitemap.xml'));

        $this->assertStringNotContainsString('Disallow: /login', $response->getContent());
    }

    public function test_optimized_hero_keeps_the_reference_dimensions_and_is_smaller(): void
    {
        $pngPath = public_path('images/landing/hero-lifers.png');
        $webpPath = public_path('images/landing/hero-lifers.webp');

        $this->assertFileExists($pngPath);
        $this->assertFileExists($webpPath);
        $this->assertSame([1672, 941], array_slice(getimagesize($pngPath), 0, 2));
        $this->assertSame([1672, 941], array_slice(getimagesize($webpPath), 0, 2));
        $this->assertLessThan(filesize($pngPath), filesize($webpPath));
    }

    public function test_brand_icons_and_manifest_are_ready_for_browsers_and_mobile_devices(): void
    {
        $expectedImages = [
            public_path('favicon.ico') => [32, 32],
            public_path('favicon-32x32.png') => [32, 32],
            public_path('favicon-192x192.png') => [192, 192],
            public_path('apple-touch-icon.png') => [180, 180],
            public_path('icon-512.png') => [512, 512],
        ];

        $this->assertFileExists(public_path('favicon.svg'));
        $this->assertFileExists(public_path('site.webmanifest'));

        foreach ($expectedImages as $path => $dimensions) {
            $this->assertFileExists($path);
            $this->assertSame($dimensions, array_slice(getimagesize($path), 0, 2));
            $this->assertGreaterThan(0, filesize($path));
        }

        $manifest = json_decode(file_get_contents(public_path('site.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('Lifers', $manifest['name']);
        $this->assertSame('#46324E', $manifest['theme_color']);
        $this->assertCount(2, $manifest['icons']);
    }
}

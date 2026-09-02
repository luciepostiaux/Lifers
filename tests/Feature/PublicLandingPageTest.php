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
                ->where('seo.description', fn (string $description) => str_contains($description, 'jeu de simulation de vie communautaire'))
                ->where('canLogin', true)
                ->where('canRegister', true));

        $response
            ->assertSee('<html lang="fr">', false)
            ->assertSee('<title data-inertia>Lifers — Ta seconde vie commence ici</title>', false)
            ->assertSee('data-inertia="description" name="description"', false)
            ->assertSee('data-inertia="robots" name="robots" content="index, follow, max-image-preview:large"', false)
            ->assertSee('data-inertia="canonical" rel="canonical" href="'.url('/').'"', false)
            ->assertSee('property="og:image" content="'.url('/images/landing/hero-lifers.png').'"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
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
}

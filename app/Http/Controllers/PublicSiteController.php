<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PublicSiteController extends Controller
{
    public function home(): InertiaResponse
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'seo' => $this->seoData(),
        ]);
    }

    public function sitemap(): Response
    {
        $homeUrl = htmlspecialchars(url('/'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{$homeUrl}</loc>
    </url>
</urlset>
XML;

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        $privatePaths = [
            '/admin',
            '/api',
            '/athome',
            '/broadcasting',
            '/character',
            '/city',
            '/consume-item',
            '/conversations',
            '/dashboard',
            '/family',
            '/job',
            '/life-gauges',
            '/lifers',
            '/mail',
            '/moderation',
            '/profil',
            '/purchase',
            '/session',
            '/social',
            '/study',
            '/treat-sickness',
            '/user',
            '/visit-doctor',
        ];

        $disallowRules = implode("\n", array_map(
            fn (string $path) => "Disallow: {$path}",
            $privatePaths,
        ));

        $content = <<<TXT
User-agent: *
Allow: /
{$disallowRules}

Sitemap: {$this->absoluteUrl('/sitemap.xml')}
TXT;

        return response($content."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function seoData(): array
    {
        $canonicalUrl = $this->absoluteUrl('/');
        $socialImageUrl = $this->absoluteUrl(config('seo.social_image'));

        return [
            'title' => config('seo.title'),
            'description' => config('seo.description'),
            'canonicalUrl' => $canonicalUrl,
            'socialImageUrl' => $socialImageUrl,
            'socialImageAlt' => config('seo.social_image_alt'),
            'locale' => config('seo.locale'),
            'alternateLocale' => config('seo.alternate_locale'),
            'author' => config('seo.author'),
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                '@id' => $canonicalUrl.'#website',
                'name' => config('seo.site_name'),
                'url' => $canonicalUrl,
                'description' => config('seo.description'),
                'image' => $socialImageUrl,
                'inLanguage' => 'fr',
                'publisher' => [
                    '@type' => 'Person',
                    'name' => config('seo.author'),
                ],
            ],
        ];
    }

    private function absoluteUrl(string $path): string
    {
        $baseUrl = rtrim(url('/'), '/');

        return $path === '/'
            ? $baseUrl
            : $baseUrl.'/'.ltrim($path, '/');
    }
}

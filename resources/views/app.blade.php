<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $isPublicWelcome = ($page['component'] ?? null) === 'Welcome';
            $seo = $isPublicWelcome ? ($page['props']['seo'] ?? []) : [];
            $seoTitle = $seo['title'] ?? config('app.name', 'Lifers');
            $seoDescription = $seo['description'] ?? null;
            $canonicalUrl = $seo['canonicalUrl'] ?? null;
            $socialImageUrl = $seo['socialImageUrl'] ?? null;
            $websiteStructuredData = $isPublicWelcome
                ? json_encode(
                    $seo['structuredData'] ?? [],
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
                )
                : null;
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#F4EEE5">
        <meta name="application-name" content="Lifers">
        <link rel="icon" href="/favicon.ico" sizes="32x32">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">

        @if (config('broadcasting.default') === 'pusher' && filled(config('broadcasting.connections.pusher.key')))
            <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
            <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'eu') }}">
        @endif

        <title data-inertia>{{ $seoTitle }}</title>

        @if ($isPublicWelcome)
            <meta data-inertia="description" name="description" content="{{ $seoDescription }}">
            <meta data-inertia="author" name="author" content="{{ $seo['author'] }}">
            <meta data-inertia="robots" name="robots" content="index, follow, max-image-preview:large">
            <link data-inertia="canonical" rel="canonical" href="{{ $canonicalUrl }}">
            <link data-inertia="alternate-fr" rel="alternate" hreflang="fr" href="{{ $canonicalUrl }}">
            <link data-inertia="alternate-default" rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">

            <meta data-inertia="og:type" property="og:type" content="website">
            <meta data-inertia="og:locale" property="og:locale" content="{{ $seo['locale'] }}">
            <meta data-inertia="og:locale:alternate" property="og:locale:alternate" content="{{ $seo['alternateLocale'] }}">
            <meta data-inertia="og:site_name" property="og:site_name" content="Lifers">
            <meta data-inertia="og:title" property="og:title" content="{{ $seoTitle }}">
            <meta data-inertia="og:description" property="og:description" content="{{ $seoDescription }}">
            <meta data-inertia="og:url" property="og:url" content="{{ $canonicalUrl }}">
            <meta data-inertia="og:image" property="og:image" content="{{ $socialImageUrl }}">
            <meta data-inertia="og:image:secure_url" property="og:image:secure_url" content="{{ $socialImageUrl }}">
            <meta data-inertia="og:image:type" property="og:image:type" content="image/png">
            <meta data-inertia="og:image:width" property="og:image:width" content="1672">
            <meta data-inertia="og:image:height" property="og:image:height" content="941">
            <meta data-inertia="og:image:alt" property="og:image:alt" content="{{ $seo['socialImageAlt'] }}">

            <meta data-inertia="twitter:card" name="twitter:card" content="summary_large_image">
            <meta data-inertia="twitter:title" name="twitter:title" content="{{ $seoTitle }}">
            <meta data-inertia="twitter:description" name="twitter:description" content="{{ $seoDescription }}">
            <meta data-inertia="twitter:image" name="twitter:image" content="{{ $socialImageUrl }}">
            <meta data-inertia="twitter:image:alt" name="twitter:image:alt" content="{{ $seo['socialImageAlt'] }}">

            <script data-inertia="website-structured-data" type="application/ld+json">{!! $websiteStructuredData !!}</script>
        @else
            <meta data-inertia="robots" name="robots" content="noindex, nofollow">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:700,800|dm-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

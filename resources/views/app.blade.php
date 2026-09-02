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
            $websiteStructuredData = $isPublicWelcome ? json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'Lifers',
                'url' => $canonicalUrl,
                'description' => $seoDescription,
                'inLanguage' => 'fr',
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#F4EEE5">

        @if (config('broadcasting.default') === 'pusher' && filled(config('broadcasting.connections.pusher.key')))
            <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
            <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'eu') }}">
        @endif

        <title data-inertia>{{ $seoTitle }}</title>

        @if ($isPublicWelcome)
            <meta data-inertia="description" name="description" content="{{ $seoDescription }}">
            <meta data-inertia="robots" name="robots" content="index, follow, max-image-preview:large">
            <link data-inertia="canonical" rel="canonical" href="{{ $canonicalUrl }}">

            <meta data-inertia="og:type" property="og:type" content="website">
            <meta data-inertia="og:locale" property="og:locale" content="fr_BE">
            <meta data-inertia="og:site_name" property="og:site_name" content="Lifers">
            <meta data-inertia="og:title" property="og:title" content="{{ $seoTitle }}">
            <meta data-inertia="og:description" property="og:description" content="{{ $seoDescription }}">
            <meta data-inertia="og:url" property="og:url" content="{{ $canonicalUrl }}">
            <meta data-inertia="og:image" property="og:image" content="{{ $socialImageUrl }}">
            <meta data-inertia="og:image:width" property="og:image:width" content="1672">
            <meta data-inertia="og:image:height" property="og:image:height" content="941">
            <meta data-inertia="og:image:alt" property="og:image:alt" content="Deux Lifers dans une ville illustrée et chaleureuse">

            <meta data-inertia="twitter:card" name="twitter:card" content="summary_large_image">
            <meta data-inertia="twitter:title" name="twitter:title" content="{{ $seoTitle }}">
            <meta data-inertia="twitter:description" name="twitter:description" content="{{ $seoDescription }}">
            <meta data-inertia="twitter:image" name="twitter:image" content="{{ $socialImageUrl }}">

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

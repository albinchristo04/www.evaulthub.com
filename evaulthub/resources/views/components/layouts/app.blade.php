<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $seo = $seo ?? [];
        $seoTitle = $seo['title'] ?? 'EVaultHub - Live Sports Streaming';
        $seoDescription = $seo['description'] ?? 'Watch live football streams online free in HD quality on EVaultHub.';
        $seoKeywords = $seo['keywords'] ?? 'live football stream, watch soccer online free, free sports streaming';
        $seoCanonical = $seo['canonical'] ?? url()->current();
        $seoImage = $seo['image'] ?? 'https://www.evaulthub.com/og-banner.jpg';
        $seoType = $seo['og_type'] ?? 'website';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <link rel="canonical" href="{{ $seoCanonical }}">

    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:type" content="{{ $seoType }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">

    <link rel="preconnect" href="https://pagead2.googlesyndication.com">
    <link rel="dns-prefetch" href="//pagead2.googlesyndication.com">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7025462814384100" crossorigin="anonymous"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --bg-primary: #0a0a0f;
            --bg-card: #12121e;
            --bg-elevated: #1a1a2e;
            --accent-red: #e94560;
            --accent-blue: #0078d7;
            --text-primary: #ffffff;
            --text-muted: #9ca3af;
        }
        body {
            background: radial-gradient(circle at top right, rgba(233,69,96,0.14), transparent 40%), var(--bg-primary);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .card-hover {
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: scale(1.02);
            border-color: var(--accent-red);
            box-shadow: 0 0 20px rgba(233, 69, 96, 0.2);
        }
        .text-muted {
            color: var(--text-muted);
        }
        .bg-card {
            background-color: var(--bg-card);
        }
        .bg-elevated {
            background-color: var(--bg-elevated);
        }
        .accent-red {
            color: var(--accent-red);
        }
        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #ef4444;
            display: inline-block;
            animation: pulse 1.4s ease-in-out infinite;
        }
        .share-btn {
            transition: transform 0.15s ease, box-shadow 0.2s ease;
        }
        .share-btn:hover {
            transform: scale(1.08);
        }
        .telegram-glow:hover {
            box-shadow: 0 0 25px rgba(0, 120, 215, 0.35);
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.35); }
        }
    </style>
    @stack('head')
</head>
<body class="min-h-screen pb-16 md:pb-0">
<x-nav />
<main class="mx-auto w-full max-w-7xl px-4 pb-12 pt-5 md:px-6">
    @isset($h1)
        <h1 class="mb-4 text-2xl font-bold text-white md:text-3xl">{{ $h1 }}</h1>
    @endisset
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-600 bg-green-900/30 px-4 py-3 text-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-600 bg-red-900/30 px-4 py-3 text-red-200">{{ session('error') }}</div>
    @endif
    {{ $slot }}
</main>
<x-footer />
@if(($showMobileAd ?? true) === true)
    <x-ad-mobile />
@endif
@stack('scripts')
</body>
</html>

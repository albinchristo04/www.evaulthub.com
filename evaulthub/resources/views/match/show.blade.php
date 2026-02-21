@php
    $matchUrl = route('watch', $match->slug);
    $shareText = $match->title.' - '.$matchUrl;
    $aboutText = "Watch {$match->title} live stream free in HD on EVaultHub. This {$match->league} match is available to stream online free without any subscription. Enjoy live {$match->league} football streaming in high definition.";
    $howToWatchText = "Looking to watch {$match->title} live stream free online? EVaultHub offers the best free sports streaming experience in HD quality for fans who never want to miss a kickoff. To watch {$match->team_home} vs {$match->team_away} live, simply click the play button above and select your preferred stream server or channel tab. No registration or subscription is required to watch {$match->league} matches online free, and you can switch channels instantly if you need an alternate feed. Our stream sources are refreshed regularly, helping you access stable match coverage before kickoff, during live action, and through major moments of the game. EVaultHub is optimized for desktop and mobile users, making it easy to stream football live from anywhere. If one source buffers, choose another tab in seconds and continue watching in HD. Stay with EVaultHub for fast, simple, and reliable live football streaming every day.";
@endphp

<x-layouts.app :seo="$seo" :h1="$h1">
    @push('head')
        <meta name="news_keywords" content="{{ $newsKeywords }}">
        <script type="application/ld+json">@json($sportsEventSchema)</script>
        <script type="application/ld+json">@json($breadcrumbSchema)</script>
    @endpush

    <section class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
            <x-ad-leaderboard />
            <x-ad-small />
        </div>

        @if($match->streams->isNotEmpty())
            <div class="mx-auto max-w-[960px]">
                <div class="mb-3 flex flex-wrap gap-2">
                    @foreach($match->streams as $stream)
                        <button
                            type="button"
                            class="stream-tab rounded-full border border-zinc-700 bg-zinc-900 px-4 py-2 text-sm font-semibold text-zinc-200 hover:border-[#e94560]"
                            data-stream-index="{{ $loop->index }}"
                        >
                            📺 {{ $stream->channel_name ?: 'Channel '.($loop->index + 1) }}
                        </button>
                    @endforeach
                </div>

                <div class="relative overflow-hidden rounded-[14px] border border-zinc-800 bg-black shadow-[0_0_24px_rgba(233,69,96,0.25)]">
                    <div class="relative w-full pt-[56.25%]">
                        <div id="stream-player-host" class="absolute inset-0">
                            <button id="stream-start-overlay" type="button" class="absolute inset-0 flex w-full flex-col items-center justify-center bg-gradient-to-b from-black/70 via-black/85 to-black/95 text-center">
                                <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-white/90 text-3xl text-black">▶</div>
                                <p class="text-lg font-semibold text-white">Click to Start Streaming</p>
                                <p class="mt-3 max-w-xl px-4 text-sm text-zinc-300">{{ $match->title }}</p>
                            </button>
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-sm italic text-zinc-400">Streaming provided by external source. EVaultHub does not host any content.</p>
            </div>
        @endif

        <x-ad-responsive />

        <section class="rounded-2xl border border-zinc-800 bg-card p-5">
            <h2 class="mb-4 text-xl font-bold">Match Information</h2>
            <div class="grid gap-3 md:grid-cols-2">
                <p>🏆 <span class="text-zinc-400">Competition:</span> {{ $match->league ?: 'Football' }}</p>
                <p>📡 <span class="text-zinc-400">Server:</span> {{ $match->server?->name }}</p>
                <p>📅 <span class="text-zinc-400">Match Date:</span> {{ optional($match->match_datetime)->format('M d, Y h:i A') }}</p>
                <p>🌍 <span class="text-zinc-400">Country:</span> {{ $match->country ?: 'International' }}</p>
                <p>📶 <span class="text-zinc-400">Stream Quality:</span> HD</p>
            </div>
            <p class="mt-4 text-sm text-zinc-300">{{ $aboutText }}</p>
        </section>

        <section class="rounded-2xl border border-zinc-800 bg-card p-5">
            <h2 class="mb-4 text-xl font-bold">Share this Match</h2>
            <div class="flex flex-wrap gap-3">
                <a class="share-btn rounded-full bg-green-600 px-4 py-2 text-sm font-semibold" target="_blank" rel="noopener noreferrer" href="https://wa.me/?text={{ urlencode($shareText) }}">🟢 WhatsApp</a>
                <a class="share-btn rounded-full bg-sky-500 px-4 py-2 text-sm font-semibold" target="_blank" rel="noopener noreferrer" href="https://t.me/share/url?url={{ urlencode($matchUrl) }}&text={{ urlencode($match->title) }}">🔵 Telegram</a>
                <a class="share-btn rounded-full bg-zinc-700 px-4 py-2 text-sm font-semibold" target="_blank" rel="noopener noreferrer" href="https://twitter.com/intent/tweet?url={{ urlencode($matchUrl) }}&text={{ urlencode($match->title) }}">🐦 Twitter/X</a>
                <a class="share-btn rounded-full bg-blue-700 px-4 py-2 text-sm font-semibold" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($matchUrl) }}">🔵 Facebook</a>
                <button id="copy-link-btn" class="share-btn rounded-full bg-zinc-800 px-4 py-2 text-sm font-semibold">🔗 Copy Link</button>
            </div>
        </section>

        <section class="rounded-2xl border border-blue-800/60 bg-blue-900/20 p-6">
            <div class="text-4xl">📲</div>
            <h2 class="mt-2 text-2xl font-bold">Get Instant Match Alerts &amp; New Streams</h2>
            <p class="mt-2 max-w-3xl text-zinc-300">Join our Telegram channel for live stream notifications, new match links, and real-time updates.</p>
            <a href="https://t.me/+brOxYHl33qljZTQ1" target="_blank" rel="noopener noreferrer" class="telegram-glow mt-4 inline-block rounded-full bg-[#0078d7] px-7 py-3 text-base font-bold">
                ⚡ Join Now on Telegram
            </a>
        </section>

        <section class="rounded-2xl border border-zinc-800 bg-card p-5">
            <h2 class="mb-3 text-2xl font-bold">How to Watch {{ $match->title }} Live Stream Free Online</h2>
            <p class="leading-7 text-zinc-300">{{ $howToWatchText }}</p>
        </section>

        <section>
            <h2 class="mb-4 text-2xl font-bold">You May Also Like</h2>
            <div class="flex snap-x gap-4 overflow-x-auto pb-4">
                @forelse($relatedMatches as $related)
                    <a href="{{ route('watch', $related->slug) }}" class="card-hover snap-start rounded-xl border border-zinc-800 bg-card p-4" style="min-width:220px;">
                        <span class="inline-block rounded-full bg-zinc-800 px-3 py-1 text-xs font-semibold">{{ $related->league ?: 'Football' }}</span>
                        <h3 class="mt-2 line-clamp-2 text-sm font-bold">{{ $related->title }}</h3>
                        <p class="mt-3 inline-block rounded bg-zinc-900 px-2 py-1 text-xs text-zinc-300">{{ optional($related->match_datetime)->format('M d, h:i A') }}</p>
                    </a>
                @empty
                    <p class="text-zinc-400">No related matches found.</p>
                @endforelse
            </div>
        </section>

        <x-ad-bottom />
    </section>

    @push('scripts')
        @if($hasM3u8)
            <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
        @endif
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const streams = @json($match->streams->map(fn($stream) => [
                    'channel_name' => $stream->channel_name,
                    'iframe_url' => $stream->iframe_url,
                    'stream_type' => $stream->stream_type,
                ])->values());
                const tabs = document.querySelectorAll('.stream-tab');
                const host = document.getElementById('stream-player-host');
                const overlay = document.getElementById('stream-start-overlay');
                const copyButton = document.getElementById('copy-link-btn');
                let activeIndex = 0;
                let started = false;
                let hlsInstance = null;

                function appendAutoplay(url) {
                    try {
                        const parsed = new URL(url);
                        parsed.searchParams.set('autoplay', '1');
                        return parsed.toString();
                    } catch (e) {
                        return url.includes('?') ? url + '&autoplay=1' : url + '?autoplay=1';
                    }
                }

                function setActiveTab(index) {
                    tabs.forEach((tab, i) => {
                        tab.classList.toggle('bg-[#e94560]', i === index);
                        tab.classList.toggle('border-[#e94560]', i === index);
                        tab.classList.toggle('text-white', i === index);
                    });
                }

                function renderStream() {
                    if (!started || !host || !streams[activeIndex]) {
                        return;
                    }

                    if (hlsInstance) {
                        hlsInstance.destroy();
                        hlsInstance = null;
                    }

                    const stream = streams[activeIndex];
                    host.innerHTML = '';

                    if (stream.stream_type === 'm3u8') {
                        const video = document.createElement('video');
                        video.controls = true;
                        video.autoplay = true;
                        video.setAttribute('playsinline', 'true');
                        video.className = 'absolute left-0 top-0 h-full w-full';
                        host.appendChild(video);

                        if (window.Hls && window.Hls.isSupported()) {
                            hlsInstance = new window.Hls();
                            hlsInstance.loadSource(stream.iframe_url);
                            hlsInstance.attachMedia(video);
                        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                            video.src = stream.iframe_url;
                        }
                    } else {
                        const iframe = document.createElement('iframe');
                        iframe.src = appendAutoplay(stream.iframe_url);
                        iframe.allowFullscreen = true;
                        iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
                        iframe.setAttribute('scrolling', 'no');
                        iframe.style.position = 'absolute';
                        iframe.style.top = '0';
                        iframe.style.left = '0';
                        iframe.style.width = '100%';
                        iframe.style.height = '100%';
                        iframe.style.border = '0';
                        host.appendChild(iframe);
                    }
                }

                tabs.forEach((tab) => {
                    tab.addEventListener('click', function () {
                        activeIndex = Number(this.dataset.streamIndex || 0);
                        setActiveTab(activeIndex);
                        renderStream();
                    });
                });

                if (overlay) {
                    overlay.addEventListener('click', function () {
                        started = true;
                        overlay.remove();
                        renderStream();
                    });
                }

                if (copyButton) {
                    copyButton.addEventListener('click', function () {
                        navigator.clipboard.writeText(@json($matchUrl)).then(() => {
                            copyButton.textContent = '✅ Copied!';
                            setTimeout(() => copyButton.textContent = '🔗 Copy Link', 1600);
                        });
                    });
                }

                setActiveTab(0);
            });
        </script>
    @endpush
</x-layouts.app>

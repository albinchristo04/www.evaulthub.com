<x-layouts.app :seo="$seo" :h1="$h1">
    <x-ad-leaderboard />

    <section class="mt-4">
        <div class="mb-4 flex flex-wrap gap-3">
            @foreach($servers as $server)
                <button
                    type="button"
                    class="server-tab rounded-full border border-zinc-700 px-4 py-2 text-sm font-semibold text-zinc-200 transition hover:border-[#e94560] {{ $loop->first ? 'bg-[#e94560] text-white border-[#e94560]' : 'bg-zinc-900' }}"
                    data-server-target="server-{{ $server->id }}"
                >
                    📡 {{ $server->name }}
                </button>
            @endforeach
        </div>

        @foreach($servers as $server)
            @php
                $matches = $matchesByServer[$server->id] ?? collect();
                $leagues = $leaguesByServer[$server->id] ?? [];
            @endphp
            <div class="server-panel {{ $loop->first ? '' : 'hidden' }}" id="server-{{ $server->id }}" data-server-id="{{ $server->id }}">
                <div class="mb-5 flex flex-wrap gap-2">
                    <button class="league-pill rounded-full border border-zinc-700 bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-white" data-league="all">All Leagues</button>
                    @foreach($leagues as $league)
                        <button class="league-pill rounded-full border border-zinc-700 bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-zinc-200" data-league="{{ \Illuminate\Support\Str::slug($league) }}">{{ $league }}</button>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @forelse($matches as $match)
                        <div class="match-card-item" data-league="{{ \Illuminate\Support\Str::slug($match->league ?: 'other') }}">
                            <x-match-card :match="$match" />
                        </div>

                        @if($loop->iteration % 6 === 0)
                            <div class="md:col-span-2 lg:col-span-3">
                                <x-ad-responsive />
                            </div>
                        @endif
                    @empty
                        <div class="col-span-full rounded-xl border border-zinc-800 bg-card p-5 text-zinc-300">
                            No matches available for {{ $server->name }}.
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tabs = document.querySelectorAll('.server-tab');
                const panels = document.querySelectorAll('.server-panel');

                tabs.forEach((tab) => {
                    tab.addEventListener('click', () => {
                        tabs.forEach((item) => item.classList.remove('bg-[#e94560]', 'text-white', 'border-[#e94560]'));
                        tab.classList.add('bg-[#e94560]', 'text-white', 'border-[#e94560]');

                        const target = tab.dataset.serverTarget;
                        panels.forEach((panel) => {
                            panel.classList.toggle('hidden', panel.id !== target);
                        });
                    });
                });

                document.querySelectorAll('.server-panel').forEach((panel) => {
                    const pills = panel.querySelectorAll('.league-pill');
                    const cards = panel.querySelectorAll('.match-card-item');

                    pills.forEach((pill) => {
                        pill.addEventListener('click', function () {
                            pills.forEach((item) => item.classList.remove('border-[#e94560]', 'text-white'));
                            this.classList.add('border-[#e94560]', 'text-white');

                            const selectedLeague = this.dataset.league;
                            cards.forEach((card) => {
                                const cardLeague = card.dataset.league;
                                const show = selectedLeague === 'all' || selectedLeague === cardLeague;
                                card.classList.toggle('hidden', !show);
                            });
                        });
                    });
                });
            });
        </script>
    @endpush
</x-layouts.app>

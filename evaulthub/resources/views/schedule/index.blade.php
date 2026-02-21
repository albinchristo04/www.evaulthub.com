<x-layouts.app :seo="$seo" :h1="$h1">
    <x-ad-leaderboard />

    <section class="mt-5 space-y-8">
        @forelse($groupedMatches as $league => $leagueMatches)
            <div>
                <h2 class="mb-4 text-xl font-bold">{{ $league }}</h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($leagueMatches as $match)
                        <x-match-card :match="$match" />
                    @endforeach
                </div>
            </div>
            @if($loop->iteration === 2)
                <x-ad-responsive />
            @endif
        @empty
            <p class="rounded-xl border border-zinc-800 bg-card p-5 text-zinc-300">No scheduled matches available right now.</p>
        @endforelse
    </section>

    <div class="mt-6">{{ $matches->links() }}</div>
</x-layouts.app>

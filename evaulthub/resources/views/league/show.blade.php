<x-layouts.app :seo="$seo" :h1="$h1">
    <x-ad-leaderboard />

    <section class="mt-5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($matches as $match)
                <x-match-card :match="$match" />
                @if($loop->iteration === 12)
                    <div class="md:col-span-2 lg:col-span-3">
                        <x-ad-responsive />
                    </div>
                @endif
            @empty
                <p class="col-span-full rounded-xl border border-zinc-800 bg-card p-5 text-zinc-300">No matches found for {{ $leagueName }}.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $matches->links() }}</div>
    </section>
</x-layouts.app>

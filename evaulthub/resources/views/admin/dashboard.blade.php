<x-layouts.admin title="Dashboard">
    <h1 class="mb-6 text-2xl font-bold">Dashboard</h1>

    @php
        $serverText = collect($stats['matches_per_server'] ?? [])
            ->map(fn($item) => "{$item['name']}: {$item['total_matches']}")
            ->implode(' | ');
    @endphp

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-800 bg-[#12121e] p-5">
            <p class="text-sm text-zinc-400">Total Matches in DB</p>
            <p class="mt-2 text-3xl font-bold">{{ $stats['total_matches'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-800 bg-[#12121e] p-5">
            <p class="text-sm text-zinc-400">Total Views Today</p>
            <p class="mt-2 text-3xl font-bold">{{ $stats['views_today'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-800 bg-[#12121e] p-5">
            <p class="text-sm text-zinc-400">Total Matches per Server</p>
            <p class="mt-2 text-base font-semibold">{{ $serverText }}</p>
        </div>
        <div class="rounded-xl border border-zinc-800 bg-[#12121e] p-5">
            <p class="text-sm text-zinc-400">Active Streams</p>
            <p class="mt-2 text-3xl font-bold">{{ $stats['active_streams'] }}</p>
        </div>
    </div>
</x-layouts.admin>

@props(['match'])

@php
    $palette = ['bg-red-700/60', 'bg-blue-700/60', 'bg-emerald-700/60', 'bg-amber-700/60', 'bg-fuchsia-700/60'];
    $leagueIndex = strlen((string) $match->league) % count($palette);
    $leagueClass = $palette[$leagueIndex];
    $status = $match->computed_status;
    $statusClass = match ($status) {
        'live' => 'bg-red-600/20 text-red-300 border border-red-500/50',
        'finished' => 'bg-zinc-700/20 text-zinc-400 border border-zinc-600/40',
        default => 'bg-zinc-700/20 text-zinc-300 border border-zinc-600/40'
    };
@endphp

<a href="{{ route('watch', $match->slug) }}" class="card-hover block rounded-xl border border-zinc-800 bg-card p-4">
    <div class="mb-3 flex items-start justify-between gap-3">
        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $leagueClass }}">{{ $match->league ?: 'Football' }}</span>
        <span class="rounded-md bg-zinc-900 px-2 py-1 text-xs text-zinc-300">{{ $match->streams_count }} streams</span>
    </div>
    <h3 class="line-clamp-2 text-xl font-bold leading-snug text-white">
        {{ $match->team_home && $match->team_away ? $match->team_home.' vs '.$match->team_away : $match->title }}
    </h3>
    <p class="mt-2 text-sm text-zinc-300">
        {{ optional($match->match_datetime)->format('M d, Y h:i A') ?: 'Time TBD' }}
    </p>
    <div class="mt-3 flex items-center justify-between">
        <span class="text-sm">{{ $match->country_flag }} {{ $match->country ?: 'International' }}</span>
        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">
            @if($status === 'live')
                <span class="pulse-dot mr-1"></span> LIVE
            @elseif($status === 'finished')
                FINISHED
            @else
                UPCOMING
            @endif
        </span>
    </div>
</a>

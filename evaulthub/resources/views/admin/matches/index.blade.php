<x-layouts.admin title="Manage Matches">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Manage Matches</h1>
        <a href="{{ route('admin.matches.create') }}" class="rounded-lg bg-[#e94560] px-4 py-2 font-semibold">Create New Match</a>
    </div>

    <form method="GET" action="{{ route('admin.matches') }}" class="mb-5 grid gap-3 rounded-xl border border-zinc-800 bg-[#12121e] p-4 md:grid-cols-4">
        <select name="server_id" class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
            <option value="">Filter by Server</option>
            @foreach($servers as $server)
                <option value="{{ $server->id }}" @selected(($filters['server_id'] ?? null) == $server->id)>{{ $server->name }}</option>
            @endforeach
        </select>
        <select name="league" class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
            <option value="">Filter by League</option>
            @foreach($leagues as $league)
                <option value="{{ $league }}" @selected(($filters['league'] ?? null) == $league)>{{ $league }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by title" class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
        <div class="md:col-span-4 flex gap-2">
            <button type="submit" class="rounded bg-blue-700 px-4 py-2 text-sm font-semibold">Apply Filters</button>
            <a href="{{ route('admin.matches') }}" class="rounded border border-zinc-700 px-4 py-2 text-sm">Reset</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-zinc-800">
        <table class="min-w-full divide-y divide-zinc-800 text-sm">
            <thead class="bg-[#0f0f19] text-zinc-300">
            <tr>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">Title</th>
                <th class="px-4 py-3 text-left">League</th>
                <th class="px-4 py-3 text-left">Server</th>
                <th class="px-4 py-3 text-left">DateTime</th>
                <th class="px-4 py-3 text-left">Streams</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 bg-[#12121e]">
            @forelse($matches as $match)
                <tr class="{{ $match->trashed() ? 'bg-red-900/10 text-red-200' : '' }}">
                    <td class="px-4 py-3">{{ $match->id }}</td>
                    <td class="px-4 py-3">{{ $match->title }}</td>
                    <td class="px-4 py-3">{{ $match->league }}</td>
                    <td class="px-4 py-3">{{ $match->server?->name }}</td>
                    <td class="px-4 py-3">{{ optional($match->match_datetime)->format('Y-m-d h:i A') }}</td>
                    <td class="px-4 py-3">{{ $match->streams_count }}</td>
                    <td class="px-4 py-3">{{ strtoupper($match->computed_status) }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.matches.edit', $match->id) }}" class="rounded bg-blue-700 px-2.5 py-1 text-xs font-semibold">Edit</a>
                            @if(!$match->trashed())
                                <form method="POST" action="{{ route('admin.matches.destroy', $match->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded bg-red-700 px-2.5 py-1 text-xs font-semibold" onclick="return confirm('Delete this match?')">Delete</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.matches.restore', $match->id) }}">
                                    @csrf
                                    <button type="submit" class="rounded bg-emerald-700 px-2.5 py-1 text-xs font-semibold">Restore</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-zinc-400">No matches found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $matches->links() }}</div>
</x-layouts.admin>

<x-layouts.admin title="Create Match">
    <h1 class="mb-6 text-2xl font-bold">Create New Match</h1>

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-600 bg-red-900/20 px-4 py-3 text-red-200">{{ $errors->first() }}</div>
    @endif

    @php
        $streams = old('streams', [
            ['channel_name' => '', 'iframe_url' => '', 'stream_type' => 'iframe'],
        ]);
    @endphp

    <form method="POST" action="{{ route('admin.matches.store') }}" class="space-y-5 rounded-xl border border-zinc-800 bg-[#12121e] p-6">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm">League</label>
                <input type="text" name="league" value="{{ old('league') }}" class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm">Country</label>
                <input type="text" name="country" value="{{ old('country') }}" class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm">Team Home</label>
                <input type="text" name="team_home" value="{{ old('team_home') }}" class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm">Team Away</label>
                <input type="text" name="team_away" value="{{ old('team_away') }}" class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm">Match Date</label>
                <input type="date" name="match_date" value="{{ old('match_date') }}" required class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm">Match Time</label>
                <input type="time" name="match_time" value="{{ old('match_time') }}" required class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm">Server</label>
                <select name="server_id" required class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2">
                    <option value="">Choose server</option>
                    @foreach($servers as $server)
                        <option value="{{ $server->id }}" @selected(old('server_id') == $server->id)>{{ $server->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" id="is_featured" name="is_featured" value="1" @checked(old('is_featured'))>
                <label for="is_featured">Featured Match</label>
            </div>
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Streams</h2>
                <button type="button" id="add-stream-btn" class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm">+ Add Stream</button>
            </div>
            <div id="streams-wrapper" class="space-y-3">
                @foreach($streams as $index => $stream)
                    <div class="stream-row grid gap-3 rounded-lg border border-zinc-800 bg-zinc-900/50 p-3 md:grid-cols-12">
                        <input type="text" name="streams[{{ $index }}][channel_name]" value="{{ $stream['channel_name'] }}" placeholder="Channel Name" class="md:col-span-3 rounded border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm">
                        <input type="url" name="streams[{{ $index }}][iframe_url]" value="{{ $stream['iframe_url'] }}" required placeholder="Iframe or m3u8 URL" class="md:col-span-6 rounded border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm">
                        <select name="streams[{{ $index }}][stream_type]" class="md:col-span-2 rounded border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm">
                            <option value="iframe" @selected(($stream['stream_type'] ?? 'iframe') === 'iframe')>iframe</option>
                            <option value="m3u8" @selected(($stream['stream_type'] ?? '') === 'm3u8')>m3u8</option>
                        </select>
                        <button type="button" class="remove-stream-btn md:col-span-1 rounded bg-red-700 px-2 py-2 text-xs font-semibold">Remove</button>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="rounded-lg bg-[#e94560] px-5 py-2.5 font-semibold">Create Match</button>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const wrapper = document.getElementById('streams-wrapper');
                const addButton = document.getElementById('add-stream-btn');

                function bindRemoveHandlers() {
                    wrapper.querySelectorAll('.remove-stream-btn').forEach((button) => {
                        button.onclick = function () {
                            const rows = wrapper.querySelectorAll('.stream-row');
                            if (rows.length > 1) {
                                this.closest('.stream-row').remove();
                                reindex();
                            }
                        };
                    });
                }

                function reindex() {
                    wrapper.querySelectorAll('.stream-row').forEach((row, index) => {
                        row.querySelectorAll('input, select').forEach((input) => {
                            const name = input.getAttribute('name');
                            input.setAttribute('name', name.replace(/streams\[\d+]/, `streams[${index}]`));
                        });
                    });
                }

                addButton.addEventListener('click', function () {
                    const index = wrapper.querySelectorAll('.stream-row').length;
                    const div = document.createElement('div');
                    div.className = 'stream-row grid gap-3 rounded-lg border border-zinc-800 bg-zinc-900/50 p-3 md:grid-cols-12';
                    div.innerHTML = `
                        <input type="text" name="streams[${index}][channel_name]" placeholder="Channel Name" class="md:col-span-3 rounded border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm">
                        <input type="url" name="streams[${index}][iframe_url]" required placeholder="Iframe or m3u8 URL" class="md:col-span-6 rounded border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm">
                        <select name="streams[${index}][stream_type]" class="md:col-span-2 rounded border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm">
                            <option value="iframe">iframe</option>
                            <option value="m3u8">m3u8</option>
                        </select>
                        <button type="button" class="remove-stream-btn md:col-span-1 rounded bg-red-700 px-2 py-2 text-xs font-semibold">Remove</button>
                    `;
                    wrapper.appendChild(div);
                    bindRemoveHandlers();
                });

                bindRemoveHandlers();
            });
        </script>
    @endpush
</x-layouts.admin>

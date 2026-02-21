<x-layouts.admin title="Import Preview">
    <h1 class="mb-6 text-2xl font-bold">Import Preview - {{ $server->name }}</h1>

    <form method="POST" action="{{ route('admin.import.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="server_id" value="{{ $server->id }}">

        <div class="flex flex-wrap gap-2">
            <button type="button" id="select-all-btn" class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm">Select All</button>
            <button type="button" id="select-new-btn" class="rounded border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm">Select New Only</button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-zinc-800">
            <table class="min-w-full divide-y divide-zinc-800 text-sm">
                <thead class="bg-[#0f0f19] text-zinc-300">
                <tr>
                    <th class="px-4 py-3 text-left">☐</th>
                    <th class="px-4 py-3 text-left">Match Title</th>
                    <th class="px-4 py-3 text-left">League</th>
                    <th class="px-4 py-3 text-left">Date/Time</th>
                    <th class="px-4 py-3 text-left">Streams Count</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800 bg-[#12121e]">
                @foreach($previewItems as $item)
                    <tr>
                        <td class="px-4 py-3">
                            <input type="checkbox" name="selected[]" value="{{ $item['index'] }}" class="import-checkbox h-4 w-4" data-is-new="{{ $item['is_existing'] ? '0' : '1' }}" {{ $item['is_existing'] ? '' : 'checked' }}>
                        </td>
                        <td class="px-4 py-3">{{ $item['title'] }}</td>
                        <td class="px-4 py-3">{{ $item['league'] ?: '-' }}</td>
                        <td class="px-4 py-3">{{ optional($item['match_datetime'])->format('Y-m-d h:i A') ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $item['streams_count'] }}</td>
                        <td class="px-4 py-3">
                            @if($item['is_existing'])
                                <span class="rounded-full bg-amber-700/30 px-2 py-1 text-xs text-amber-300">⚠️ Already Imported</span>
                            @else
                                <span class="rounded-full bg-emerald-700/30 px-2 py-1 text-xs text-emerald-300">✅ New</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="rounded-lg bg-[#e94560] px-5 py-2.5 font-semibold">Import Selected Matches</button>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const checkboxes = Array.from(document.querySelectorAll('.import-checkbox'));
                const selectAllButton = document.getElementById('select-all-btn');
                const selectNewButton = document.getElementById('select-new-btn');

                selectAllButton?.addEventListener('click', () => {
                    checkboxes.forEach((box) => box.checked = true);
                });

                selectNewButton?.addEventListener('click', () => {
                    checkboxes.forEach((box) => box.checked = box.dataset.isNew === '1');
                });
            });
        </script>
    @endpush
</x-layouts.admin>

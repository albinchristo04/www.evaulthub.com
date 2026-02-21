<x-layouts.admin title="Import Matches">
    <h1 class="mb-6 text-2xl font-bold">Import Matches</h1>

    <div class="rounded-xl border border-zinc-800 bg-[#12121e] p-6">
        <h2 class="text-lg font-semibold">Step 1 - Select Server to Import From</h2>
        <form method="POST" action="{{ route('admin.import.fetch') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="mb-2 block text-sm text-zinc-300">Select Server</label>
                <select name="server_id" required class="w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-white focus:border-zinc-500 focus:outline-none">
                    <option value="">Choose server</option>
                    @foreach($servers as $server)
                        <option value="{{ $server->id }}">{{ $server->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-[#e94560] px-5 py-2.5 font-semibold">Fetch Matches</button>
        </form>
    </div>
</x-layouts.admin>

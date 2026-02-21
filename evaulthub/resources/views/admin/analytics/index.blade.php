<x-layouts.admin title="Analytics">
    <h1 class="mb-6 text-2xl font-bold">Analytics</h1>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-800 bg-[#12121e] p-5">
            <p class="text-sm text-zinc-400">Today's Views</p>
            <p class="mt-2 text-3xl font-bold">{{ $cards['today'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-800 bg-[#12121e] p-5">
            <p class="text-sm text-zinc-400">Yesterday's Views</p>
            <p class="mt-2 text-3xl font-bold">{{ $cards['yesterday'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-800 bg-[#12121e] p-5">
            <p class="text-sm text-zinc-400">Last 7 Days Total</p>
            <p class="mt-2 text-3xl font-bold">{{ $cards['last7'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-800 bg-[#12121e] p-5">
            <p class="text-sm text-zinc-400">All Time Total</p>
            <p class="mt-2 text-3xl font-bold">{{ $cards['all_time'] }}</p>
        </div>
    </div>

    <section class="mt-8 overflow-x-auto rounded-xl border border-zinc-800">
        <h2 class="border-b border-zinc-800 bg-[#0f0f19] px-4 py-3 text-lg font-semibold">Server Breakdown</h2>
        <table class="min-w-full divide-y divide-zinc-800 text-sm">
            <thead class="bg-[#0f0f19] text-zinc-300">
            <tr>
                <th class="px-4 py-3 text-left">Server Name</th>
                <th class="px-4 py-3 text-left">Views Today</th>
                <th class="px-4 py-3 text-left">Views Yesterday</th>
                <th class="px-4 py-3 text-left">Views 7 Days</th>
                <th class="px-4 py-3 text-left">Total Views</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 bg-[#12121e]">
            @foreach($serverBreakdown as $row)
                <tr>
                    <td class="px-4 py-3">{{ $row->name }}</td>
                    <td class="px-4 py-3">{{ $row->views_today }}</td>
                    <td class="px-4 py-3">{{ $row->views_yesterday }}</td>
                    <td class="px-4 py-3">{{ $row->views_7_days }}</td>
                    <td class="px-4 py-3">{{ $row->total_views }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section class="mt-8 overflow-x-auto rounded-xl border border-zinc-800">
        <h2 class="border-b border-zinc-800 bg-[#0f0f19] px-4 py-3 text-lg font-semibold">Top 10 Matches (Last 7 Days)</h2>
        <table class="min-w-full divide-y divide-zinc-800 text-sm">
            <thead class="bg-[#0f0f19] text-zinc-300">
            <tr>
                <th class="px-4 py-3 text-left">Rank</th>
                <th class="px-4 py-3 text-left">Match Title</th>
                <th class="px-4 py-3 text-left">Server</th>
                <th class="px-4 py-3 text-left">Views (7d)</th>
                <th class="px-4 py-3 text-left">Total Views</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 bg-[#12121e]">
            @foreach($topMatches as $index => $match)
                <tr>
                    <td class="px-4 py-3">{{ $index + 1 }}</td>
                    <td class="px-4 py-3">
                        {{ $match['match_title'] }}
                        @if($match['deleted'])
                            <span class="ml-2 rounded bg-zinc-700 px-2 py-0.5 text-xs text-zinc-300">(deleted)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $match['server_name'] }}</td>
                    <td class="px-4 py-3">{{ $match['views_7_days'] }}</td>
                    <td class="px-4 py-3">{{ $match['total_views'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section class="mt-8 rounded-xl border border-zinc-800 bg-[#12121e] p-5">
        <h2 class="mb-4 text-lg font-semibold">Views Chart (Last 7 Days)</h2>
        <canvas id="views-chart" height="100"></canvas>
    </section>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('views-chart');
                if (!ctx) return;

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($chart['labels']),
                        datasets: [
                            {
                                label: 'Server 1',
                                backgroundColor: '#0078d7',
                                data: @json($chart['server1']),
                            },
                            {
                                label: 'Server 2',
                                backgroundColor: '#e94560',
                                data: @json($chart['server2']),
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#d4d4d8' },
                                grid: { color: 'rgba(255,255,255,0.08)' }
                            },
                            x: {
                                ticks: { color: '#d4d4d8' },
                                grid: { color: 'rgba(255,255,255,0.05)' }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: { color: '#ffffff' }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-layouts.admin>

<?php

namespace App\Services;

use App\Models\MatchModel;
use App\Models\MatchStream;
use App\Models\MatchView;
use App\Models\Server;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * @return array<string, int|array<int, array<string, mixed>>>
     */
    public function dashboardStats(): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $matchesPerServer = Server::query()
            ->select('servers.id', 'servers.name')
            ->selectRaw('COUNT(matches.id) as total_matches')
            ->leftJoin('matches', function ($join) {
                $join->on('matches.server_id', '=', 'servers.id')
                    ->whereNull('matches.deleted_at');
            })
            ->groupBy('servers.id', 'servers.name')
            ->orderBy('servers.id')
            ->get()
            ->map(function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'total_matches' => (int) $row->total_matches,
                ];
            })
            ->all();

        return [
            'total_matches' => MatchModel::query()->count(),
            'views_today' => MatchView::query()
                ->whereBetween('viewed_at', [$todayStart, $todayEnd])
                ->count(),
            'active_streams' => MatchStream::query()
                ->whereHas('match', fn ($query) => $query->whereNull('deleted_at'))
                ->count(),
            'matches_per_server' => $matchesPerServer,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function analyticsData(): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $yesterdayStart = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();
        $sevenDaysAgo = now()->subDays(6)->startOfDay();

        $topCards = [
            'today' => MatchView::query()->whereBetween('viewed_at', [$todayStart, $todayEnd])->count(),
            'yesterday' => MatchView::query()->whereBetween('viewed_at', [$yesterdayStart, $yesterdayEnd])->count(),
            'last7' => MatchView::query()->where('viewed_at', '>=', $sevenDaysAgo)->count(),
            'all_time' => MatchView::query()->count(),
        ];

        $serverBreakdown = Server::query()
            ->select('servers.id', 'servers.name')
            ->selectRaw('SUM(CASE WHEN match_views.viewed_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as views_today', [$todayStart, $todayEnd])
            ->selectRaw('SUM(CASE WHEN match_views.viewed_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as views_yesterday', [$yesterdayStart, $yesterdayEnd])
            ->selectRaw('SUM(CASE WHEN match_views.viewed_at >= ? THEN 1 ELSE 0 END) as views_7_days', [$sevenDaysAgo])
            ->selectRaw('COUNT(match_views.id) as total_views')
            ->leftJoin('match_views', 'match_views.server_id', '=', 'servers.id')
            ->groupBy('servers.id', 'servers.name')
            ->orderBy('servers.id')
            ->get();

        $topMatches = MatchView::query()
            ->select('match_title', 'match_id', 'server_id')
            ->selectRaw('SUM(CASE WHEN viewed_at >= ? THEN 1 ELSE 0 END) as views_7_days', [$sevenDaysAgo])
            ->selectRaw('COUNT(id) as total_views')
            ->groupBy('match_title', 'match_id', 'server_id')
            ->orderByDesc('views_7_days')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $matchDeleted = !MatchModel::query()->withTrashed()->where('id', $row->match_id)->exists()
                    || MatchModel::query()->onlyTrashed()->where('id', $row->match_id)->exists();

                return [
                    'match_title' => (string) $row->match_title,
                    'match_id' => (int) $row->match_id,
                    'server_id' => $row->server_id ? (int) $row->server_id : null,
                    'views_7_days' => (int) $row->views_7_days,
                    'total_views' => (int) $row->total_views,
                    'deleted' => $matchDeleted,
                ];
            })
            ->all();

        [$labels, $serverOneData, $serverTwoData] = $this->viewsChartData();

        return [
            'cards' => $topCards,
            'server_breakdown' => $serverBreakdown,
            'top_matches' => $topMatches,
            'chart' => [
                'labels' => $labels,
                'server1' => $serverOneData,
                'server2' => $serverTwoData,
            ],
        ];
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, int>, 2: array<int, int>}
     */
    protected function viewsChartData(): array
    {
        $dates = collect(range(0, 6))
            ->map(fn ($offset) => now()->subDays(6 - $offset)->toDateString())
            ->all();

        $rows = MatchView::query()
            ->selectRaw('DATE(viewed_at) as day')
            ->selectRaw('server_id')
            ->selectRaw('COUNT(*) as total')
            ->where('viewed_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(viewed_at)'), 'server_id')
            ->get();

        $byServerAndDay = [];
        foreach ($rows as $row) {
            $serverId = (int) ($row->server_id ?? 0);
            $day = (string) $row->day;
            $byServerAndDay[$serverId][$day] = (int) $row->total;
        }

        $serverOne = [];
        $serverTwo = [];
        foreach ($dates as $date) {
            $serverOne[] = (int) ($byServerAndDay[1][$date] ?? 0);
            $serverTwo[] = (int) ($byServerAndDay[2][$date] ?? 0);
        }

        $labels = array_map(
            fn ($date) => Carbon::parse($date)->format('M d'),
            $dates
        );

        return [$labels, $serverOne, $serverTwo];
    }
}

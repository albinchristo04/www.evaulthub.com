<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {
    }

    public function index(): View
    {
        $analytics = $this->analyticsService->analyticsData();
        $serverMap = Server::query()->pluck('name', 'id');

        $topMatches = collect($analytics['top_matches'])
            ->map(function (array $row) use ($serverMap) {
                $row['server_name'] = $serverMap[$row['server_id']] ?? 'Unknown';
                return $row;
            })
            ->all();

        return view('admin.analytics.index', [
            'cards' => $analytics['cards'],
            'serverBreakdown' => $analytics['server_breakdown'],
            'topMatches' => $topMatches,
            'chart' => $analytics['chart'],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {
    }

    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => $this->analyticsService->dashboardStats(),
        ]);
    }
}

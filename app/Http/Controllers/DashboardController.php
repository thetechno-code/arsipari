<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Show the main dashboard with statistics and recent archive discovery.
     */
    public function index(): View
    {
        $user               = auth()->user()->load('department');
        $summary            = $this->dashboardService->getSummary();
        $recentArchives     = $this->dashboardService->getRecentArchives(10);
        $categoryStatistics = $this->dashboardService->getCategoryStatistics(5);
        $yearStatistics     = $this->dashboardService->getYearStatistics(5);

        return view('dashboard.index', compact(
            'user',
            'summary',
            'recentArchives',
            'categoryStatistics',
            'yearStatistics'
        ));
    }
}

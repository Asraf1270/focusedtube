<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics)
    {
    }

    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => $this->analytics->dashboardOverview(),
        ]);
    }
}
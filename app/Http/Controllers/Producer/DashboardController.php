<?php

namespace App\Http\Controllers\Producer;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends BaseController
{
    public function index(Request $request, AnalyticsService $analytics): View
    {
        $producer = $this->producer($request);

        return view('producer.dashboard', [
            'producer' => $producer,
            'summary' => $analytics->producerSummary($producer),
            'revenue' => $analytics->producerRevenueByDay($producer, 14),
            'topProducts' => $analytics->producerTopProducts($producer, 5),
            'recentOrders' => $producer->orders()->with(['buyer', 'delivery'])->latest()->limit(6)->get(),
        ]);
    }
}

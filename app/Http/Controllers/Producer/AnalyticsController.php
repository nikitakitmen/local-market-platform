<?php

namespace App\Http\Controllers\Producer;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends BaseController
{
    public function index(Request $request, AnalyticsService $analytics): View
    {
        $producer = $this->producer($request);

        return view('producer.analytics', [
            'summary' => $analytics->producerSummary($producer),
            'revenue' => $analytics->producerRevenueByDay($producer, 30),
            'statuses' => $analytics->producerStatusBreakdown($producer),
            'topProducts' => $analytics->producerTopProducts($producer, 10),
        ]);
    }
}

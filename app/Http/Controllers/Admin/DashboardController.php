<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\ProducerStatus;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\ProducerApplication;
use App\Models\ReviewReport;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Главная страница панели управления: у администратора — показатели платформы,
 * у оператора — очередь задач (проблемные заказы, доставки, жалобы).
 */
class DashboardController extends Controller
{
    public function index(Request $request, AnalyticsService $analytics): View
    {
        if ($request->user()->isAdmin()) {
            return view('admin.dashboard', [
                'summary' => $analytics->platformSummary(),
                'turnover' => $analytics->platformTurnoverByDay(14),
                'recentOrders' => Order::with(['buyer', 'producer', 'delivery'])->latest()->limit(8)->get(),
                'applications' => ProducerApplication::with(['producer', 'user'])->where('status', ProducerStatus::Pending)->latest()->limit(5)->get(),
            ]);
        }

        return view('admin.operator-dashboard', [
            'summary' => $analytics->operatorSummary(),
            'problemOrders' => Order::problem()->with(['buyer', 'producer', 'delivery'])->oldest()->limit(8)->get(),
            'waitingDeliveries' => Delivery::where('status', DeliveryStatus::Waiting)
                ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Accepted))
                ->with('order.producer')
                ->oldest()
                ->limit(6)
                ->get(),
            'reports' => ReviewReport::where('status', ReportStatus::Pending)->with(['review.product', 'user'])->latest()->limit(5)->get(),
        ]);
    }
}

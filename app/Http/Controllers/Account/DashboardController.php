<?php

namespace App\Http\Controllers\Account;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Обзор личного кабинета покупателя.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('account.dashboard', [
            'user' => $user,
            'stats' => [
                'orders' => $user->orders()->count(),
                'active' => $user->orders()->whereIn('status', [OrderStatus::New, OrderStatus::Accepted])->count(),
                'favorites' => $user->favorites()->count(),
                'reviews' => $user->reviews()->count(),
            ],
            'recentOrders' => $user->orders()->with(['producer', 'delivery'])->latest()->limit(4)->get(),
            'application' => $user->producerApplications()->with('producer')->latest()->first(),
        ]);
    }
}

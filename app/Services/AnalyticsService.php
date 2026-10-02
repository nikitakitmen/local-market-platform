<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\ProducerStatus;
use App\Enums\ReportStatus;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Producer;
use App\Models\ProducerApplication;
use App\Models\Product;
use App\Models\ReviewReport;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Базовая аналитика для кабинета производителя и админ-панели.
 * Выручка считается по выполненным заказам.
 */
class AnalyticsService
{
    // ---------------------------------------------------------------------
    // Производитель
    // ---------------------------------------------------------------------

    public function producerSummary(Producer $producer): array
    {
        $completed = $producer->orders()->completed();
        $completedCount = (clone $completed)->count();
        // Выручка производителя — стоимость товаров за вычетом скидки (доставку получает платформа)
        $revenue = (float) (clone $completed)->selectRaw('SUM(subtotal - discount) as revenue')->value('revenue');
        $commission = (float) (clone $completed)->sum('commission_amount');

        return [
            'orders_total' => $producer->orders()->count(),
            'orders_new' => $producer->orders()->status(OrderStatus::New)->count(),
            'orders_completed' => $completedCount,
            'revenue' => $revenue,
            'commission' => $commission,
            'payout' => $revenue - $commission,
            'average_check' => $completedCount ? round($revenue / $completedCount) : 0,
            'products_count' => $producer->products()->count(),
            'products_active' => $producer->products()->where('is_active', true)->count(),
        ];
    }

    /** Выручка по дням за последние N дней (для линейного графика). */
    public function producerRevenueByDay(Producer $producer, int $days = 30): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $rows = $producer->orders()->completed()
            ->where('completed_at', '>=', $from)
            ->selectRaw('DATE(completed_at) as day, SUM(subtotal - discount) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        return $this->fillDays($from, $days, $rows);
    }

    /** Количество заказов по статусам (для круговой диаграммы). */
    public function producerStatusBreakdown(Producer $producer): array
    {
        $counts = $producer->orders()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $values = [];

        foreach (OrderStatus::cases() as $status) {
            $labels[] = $status->label();
            $values[] = (int) ($counts[$status->value] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /** Самые продаваемые товары производителя (по выполненным заказам). */
    public function producerTopProducts(Producer $producer, int $limit = 5): Collection
    {
        return OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('producer_id', $producer->id)->where('status', OrderStatus::Completed))
            ->selectRaw('product_id, product_name, SUM(quantity) as quantity, SUM(total) as revenue')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get();
    }

    // ---------------------------------------------------------------------
    // Платформа (админка)
    // ---------------------------------------------------------------------

    public function platformSummary(): array
    {
        return [
            'users' => User::count(),
            'producers' => Producer::where('status', ProducerStatus::Approved)->count(),
            'pending_applications' => ProducerApplication::where('status', ProducerStatus::Pending)->count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'orders_active' => Order::whereIn('status', [OrderStatus::New, OrderStatus::Accepted])->count(),
            // Оборот — сумма выполненных заказов, комиссия — доля платформы с них
            'turnover' => (float) Order::completed()->sum('total'),
            'commission' => (float) Order::completed()->sum('commission_amount'),
        ];
    }

    /** Показатели для дашборда оператора. */
    public function operatorSummary(): array
    {
        return [
            'orders_new' => Order::status(OrderStatus::New)->count(),
            'orders_problem' => Order::problem()->count(),
            'deliveries_waiting' => Delivery::where('status', DeliveryStatus::Waiting)
                ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Accepted))->count(),
            'deliveries_active' => Delivery::whereIn('status', [DeliveryStatus::Assigned, DeliveryStatus::InTransit])->count(),
            'reports_pending' => ReviewReport::where('status', ReportStatus::Pending)->count(),
        ];
    }

    /** Оборот платформы по дням (для графика в админке). */
    public function platformTurnoverByDay(int $days = 14): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $rows = Order::completed()
            ->where('completed_at', '>=', $from)
            ->selectRaw('DATE(completed_at) as day, SUM(total) as turnover')
            ->groupBy('day')
            ->pluck('turnover', 'day');

        return $this->fillDays($from, $days, $rows);
    }

    /** Дополняет пропущенные дни нулями, чтобы на графике были все даты. */
    private function fillDays(Carbon $from, int $days, Collection $rows): array
    {
        $labels = [];
        $values = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i);
            $labels[] = $date->format('d.m');
            $values[] = round((float) ($rows[$date->toDateString()] ?? 0), 2);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}

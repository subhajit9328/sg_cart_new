<?php

namespace SGCart\Reporting\Actions;

use App\Enums\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use SGCart\Reporting\Queries\OrderReportQuery;

/**
 * Orchestrates all Orders Report metrics, analytics, and chart data.
 * Accepts resolved Carbon date boundaries and returns a structured data array.
 */
class GetOrderReportDataAction
{
    public function __construct(
        protected OrderReportQuery $query
    ) {}

    /**
     * Execute the action and return all report data.
     *
     * @return array{
     *     date_from: Carbon,
     *     date_to: Carbon,
     *     days_in_range: int,
     *     metrics: array,
     *     revenue_analytics: array,
     *     chart_labels: array,
     *     chart_revenue: array,
     *     chart_orders: array,
     *     chart_status_labels: array,
     *     chart_status_data: array,
     *     chart_status_colors: array,
     *     orders: LengthAwarePaginator,
     * }
     */
    public function execute(Carbon $from, Carbon $to, int $perPage = 25, ?string $search = null, ?string $sortBy = null, ?string $sortDir = null): array
    {
        // ── Core Metrics ────────────────────────────────────────────────────
        $totalOrders = $this->query->totalOrders($from, $to);
        $grossRevenue = $this->query->grossRevenue($from, $to);
        $refundAmount = $this->query->refundAmount($from, $to);
        $netRevenue = $grossRevenue - $refundAmount;
        $avgOrderValue = $totalOrders > 0 ? $grossRevenue / $totalOrders : 0.0;
        $totalItemsSold = $this->query->totalItemsSold($from, $to);

        $processing = $this->query->countByStatus($from, $to, OrderStatus::PROCESSING->value);
        $shipped = $this->query->countByStatus($from, $to, OrderStatus::SHIPPED->value);
        $delivered = $this->query->countByStatus($from, $to, OrderStatus::DELIVERED->value);
        $cancelled = $this->query->countByStatus($from, $to, OrderStatus::CANCELLED->value);

        // ── Revenue Analytics ────────────────────────────────────────────────
        $daysInRange = ceil(max(1, $from->diffInDays($to)));
        $avgRevenuePerDay = $grossRevenue / $daysInRange;

        // Previous equivalent period for growth calculation
        $prevTo = $from->copy()->subDay();
        $prevFrom = $prevTo->copy()->subDays($daysInRange - 1);
        $prevGrossRevenue = $this->query->grossRevenueForPeriod($prevFrom, $prevTo);

        $revenueGrowth = null;
        if ($prevGrossRevenue > 0) {
            $revenueGrowth = (($grossRevenue - $prevGrossRevenue) / $prevGrossRevenue) * 100;
        } elseif ($grossRevenue > 0) {
            $revenueGrowth = 100.0;
        }

        // ── Chart Data ───────────────────────────────────────────────────────
        $dateSeries = $this->query->dateSeries($from, $to);
        $revenueByDay = $this->query->revenueByDay($from, $to);
        $ordersByDay = $this->query->orderCountByDay($from, $to);

        // Fill gaps in time series with 0 so chart lines are continuous
        $chartRevenue = [];
        $chartOrders = [];
        foreach ($dateSeries as $date) {
            $chartRevenue[] = round($revenueByDay->get($date, 0), 2);
            $chartOrders[] = $ordersByDay->get($date, 0);
        }

        // Format labels — show day/month if range <= 62 days, else month/year
        $chartLabels = count($dateSeries) <= 62
            ? array_map(fn ($d) => Carbon::parse($d)->format('d M'), $dateSeries)
            : array_map(fn ($d) => Carbon::parse($d)->format('M Y'), $dateSeries);

        // Status distribution doughnut
        $statusDistribution = $this->query->statusDistribution($from, $to);
        $statusColors = [
            'Processing' => '#3b82f6',
            'Shipped' => '#8b5cf6',
            'Delivered' => '#10b981',
            'Cancelled' => '#ef4444',
        ];

        $chartStatusLabels = [];
        $chartStatusData = [];
        $chartStatusColors = [];

        foreach ($statusColors as $status => $color) {
            $count = $statusDistribution->get($status, 0);
            if ($count > 0 || $totalOrders === 0) {
                $chartStatusLabels[] = $status;
                $chartStatusData[] = $count;
                $chartStatusColors[] = $color;
            }
        }

        // ── Detailed Paginated Orders ────────────────────────────────────────
        $orders = $this->query->detailedOrdersPaginated($from, $to, $perPage, $search, $sortBy, $sortDir);

        return [
            'date_from' => $from,
            'date_to' => $to,
            'days_in_range' => $daysInRange,

            // Dashboard metric cards
            'metrics' => [
                'total_revenue' => $grossRevenue,
                'total_orders' => $totalOrders,
                'avgOrderValue' => $avgOrderValue,
                'items_sold' => $totalItemsSold,
                'processing' => $processing,
                'shipped' => $shipped,
                'delivered' => $delivered,
                'cancelled' => $cancelled,
            ],

            // Revenue analytics panel
            'revenue_analytics' => [
                'gross_revenue' => $grossRevenue,
                'net_revenue' => $netRevenue,
                'refund_amount' => $refundAmount,
                'revenue_growth' => $revenueGrowth,
                'avg_revenue_per_day' => $avgRevenuePerDay,
            ],

            // Chart.js datasets
            'chart_labels' => $chartLabels,
            'chart_revenue' => $chartRevenue,
            'chart_orders' => $chartOrders,
            'chart_status_labels' => $chartStatusLabels,
            'chart_status_data' => $chartStatusData,
            'chart_status_colors' => $chartStatusColors,

            // Paginated orders table
            'orders' => $orders,
        ];
    }
}

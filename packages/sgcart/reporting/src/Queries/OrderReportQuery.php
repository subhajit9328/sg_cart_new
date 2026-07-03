<?php

namespace SGCart\Reporting\Queries;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Reusable query builders for the Orders Report.
 * All methods are stateless and accept Carbon date boundaries.
 */
class OrderReportQuery
{
    /**
     * Base query: all orders within the given date range.
     */
    public function baseQuery(Carbon $from, Carbon $to): Builder
    {
        return Order::query()
            ->whereBetween('created_at', [
                $from->copy()->startOfDay(),
                $to->copy()->endOfDay(),
            ]);
    }

    /**
     * Sum of order totals within the date range (Gross Revenue).
     */
    public function grossRevenue(Carbon $from, Carbon $to): float
    {
        return (float) $this->baseQuery($from, $to)->sum('total');
    }

    /**
     * Sum of totals for Cancelled orders within the date range (Refund/Loss Amount).
     */
    public function refundAmount(Carbon $from, Carbon $to): float
    {
        return (float) $this->baseQuery($from, $to)
            ->where('status', 'Cancelled')
            ->sum('total');
    }

    /**
     * Total number of orders within the date range.
     */
    public function totalOrders(Carbon $from, Carbon $to): int
    {
        return $this->baseQuery($from, $to)->count();
    }

    /**
     * Total quantity of items sold across all orders in the date range.
     */
    public function totalItemsSold(Carbon $from, Carbon $to): int
    {
        return (int) $this->baseQuery($from, $to)
            ->withSum('items', 'quantity')
            ->get()
            ->sum('items_sum_quantity');
    }

    /**
     * Count of orders by a specific status within the date range.
     */
    public function countByStatus(Carbon $from, Carbon $to, string $status): int
    {
        return $this->baseQuery($from, $to)
            ->where('status', $status)
            ->count();
    }

    /**
     * Gross revenue for a previous equivalent period (for growth comparison).
     */
    public function grossRevenueForPeriod(Carbon $from, Carbon $to): float
    {
        return $this->grossRevenue($from, $to);
    }

    /**
     * Daily revenue aggregation for the chart.
     * Returns a collection keyed by date string (Y-m-d) => float revenue.
     */
    public function revenueByDay(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return $this->baseQuery($from, $to)
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date')
            ->map(fn($v) => (float) $v);
    }

    /**
     * Daily order count aggregation for the chart.
     * Returns a collection keyed by date string (Y-m-d) => int count.
     */
    public function orderCountByDay(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return $this->baseQuery($from, $to)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as order_count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('order_count', 'date')
            ->map(fn($v) => (int) $v);
    }

    /**
     * Order count grouped by status for the distribution doughnut chart.
     * Returns a collection keyed by status => int count.
     */
    public function statusDistribution(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return $this->baseQuery($from, $to)
            ->selectRaw('status, COUNT(*) as order_count')
            ->groupBy('status')
            ->pluck('order_count', 'status')
            ->map(fn($v) => (int) $v);
    }

    /**
     * Paginated detailed orders query with eager-loaded relationships.
     */
    public function detailedOrdersPaginated(Carbon $from, Carbon $to, int $perPage = 25): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->baseQuery($from, $to)
            ->with(['customer', 'payments' => fn($q) => $q->latest()->limit(1)])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Generate a complete date spine (all days) between $from and $to.
     * Returns an array of Y-m-d strings.
     */
    public function dateSeries(Carbon $from, Carbon $to): array
    {
        $dates = [];
        $current = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($current->lte($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }
}

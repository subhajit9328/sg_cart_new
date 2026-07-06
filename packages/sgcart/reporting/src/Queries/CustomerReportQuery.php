<?php

namespace SGCart\Reporting\Queries;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerReportQuery
{
    /**
     * Total distinct registered customers in database.
     */
    public function totalRegisteredCustomersCount(): int
    {
        return Customer::count();
    }

    /**
     * New customer registrations in the date range.
     */
    public function newCustomersCount(Carbon $from, Carbon $to): int
    {
        return Customer::whereBetween('created_at', [
            $from->copy()->startOfDay(),
            $to->copy()->endOfDay(),
        ])->count();
    }

    /**
     * Active customers in the range (distinct customers who ordered).
     */
    public function activeCustomersCount(Carbon $from, Carbon $to): int
    {
        return Order::whereBetween('created_at', [
            $from->copy()->startOfDay(),
            $to->copy()->endOfDay(),
        ])
        ->whereNotNull('customer_id')
        ->distinct('customer_id')
        ->count('customer_id');
    }

    /**
     * Returning customers count (ordered in range and had prior orders).
     */
    public function returningCustomersCount(Carbon $from, Carbon $to): int
    {
        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->endOfDay();

        return Order::whereBetween('created_at', [$start, $end])
            ->whereNotNull('customer_id')
            ->whereIn('customer_id', function ($query) use ($start) {
                $query->select('customer_id')
                    ->from('orders')
                    ->where('created_at', '<', $start);
            })
            ->distinct('customer_id')
            ->count('customer_id');
    }

    /**
     * Lifetime revenue (sum of all orders) for customers who placed orders in the range.
     */
    public function clvForActiveCustomers(Carbon $from, Carbon $to): float
    {
        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->endOfDay();

        $activeIds = Order::whereBetween('created_at', [$start, $end])
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id');

        if ($activeIds->isEmpty()) {
            return 0.0;
        }

        $totalLifetimeSales = Order::whereIn('customer_id', $activeIds)->sum('total');

        return (float) ($totalLifetimeSales / $activeIds->count());
    }

    /**
     * Top customers in range by purchase amount.
     */
    public function topCustomersBySales(Carbon $from, Carbon $to, int $limit = 10): \Illuminate\Support\Collection
    {
        return Order::whereBetween('created_at', [
            $from->copy()->startOfDay(),
            $to->copy()->endOfDay(),
        ])
        ->whereNotNull('customer_id')
        ->select('customer_id', DB::raw("CONCAT(first_name, ' ', last_name) as name"), DB::raw('SUM(total) as total_sales'))
        ->groupBy('customer_id', 'first_name', 'last_name')
        ->orderByDesc('total_sales')
        ->limit($limit)
        ->get();
    }

    /**
     * Top customers in range by number of orders.
     */
    public function topCustomersByOrders(Carbon $from, Carbon $to, int $limit = 10): \Illuminate\Support\Collection
    {
        return Order::whereBetween('created_at', [
            $from->copy()->startOfDay(),
            $to->copy()->endOfDay(),
        ])
        ->whereNotNull('customer_id')
        ->select('customer_id', DB::raw("CONCAT(first_name, ' ', last_name) as name"), DB::raw('COUNT(*) as total_orders'))
        ->groupBy('customer_id', 'first_name', 'last_name')
        ->orderByDesc('total_orders')
        ->limit($limit)
        ->get();
    }

    /**
     * New customer registrations grouped by day/month for growth trend.
     */
    public function customerGrowthSeries(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return Customer::whereBetween('created_at', [
            $from->copy()->startOfDay(),
            $to->copy()->endOfDay(),
        ])
        ->selectRaw('DATE(created_at) as date, COUNT(*) as new_customers')
        ->groupBy('date')
        ->orderBy('date')
        ->pluck('new_customers', 'date')
        ->map(fn($v) => (int) $v);
    }

    /**
     * Get the query builder for customer details (used for streaming CSV export).
     */
    public function customersDetailedQueryBuilder(Carbon $from, Carbon $to): Builder
    {
        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->endOfDay();

        return Order::whereBetween('created_at', [$start, $end])
            ->whereNotNull('customer_id')
            ->select(
                'customer_id',
                'first_name',
                'last_name',
                'email',
                'phone',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('AVG(total) as aov'),
                DB::raw('MAX(created_at) as last_order_date')
            )
            ->groupBy('customer_id', 'first_name', 'last_name', 'email', 'phone')
            ->orderByDesc('total_spent');
    }

    /**
     * Paginated list of customer stats within the date range.
     */
    public function customersDetailedQuery(
        Carbon $from,
        Carbon $to,
        int $perPage = 25,
        ?string $search = null,
        ?string $sortBy = null,
        ?string $sortDir = null
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->endOfDay();

        $query = Order::whereBetween('created_at', [$start, $end])
            ->whereNotNull('customer_id')
            ->select(
                'customer_id',
                'first_name',
                'last_name',
                'email',
                'phone',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('AVG(total) as aov'),
                DB::raw('MAX(created_at) as last_order_date')
            )
            ->groupBy('customer_id', 'first_name', 'last_name', 'email', 'phone');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortDir = strtolower($sortDir ?? '') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'total_orders' => 'total_orders',
            'total_spent' => 'total_spent',
            'aov' => 'aov',
            'last_order_date' => 'last_order_date',
        ];

        if ($sortBy && array_key_exists($sortBy, $allowedSorts)) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->orderByDesc('total_spent');
        }

        return $query->paginate($perPage)->withQueryString();
    }
}

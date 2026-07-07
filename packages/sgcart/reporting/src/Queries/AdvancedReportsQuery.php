<?php

namespace SGCart\Reporting\Queries;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdvancedReportsQuery
{
    /**
     * Get revenue grouped by product within date range.
     */
    public function revenueByProduct(Carbon $from, Carbon $to, int $limit = 50)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [
                $from->copy()->startOfDay(),
                $to->copy()->endOfDay(),
            ])
            ->where('orders.status', '!=', 'Cancelled')
            ->select(
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.price * order_items.quantity) as gross_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('gross_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get revenue grouped by product category within date range.
     */
    public function revenueByCategory(Carbon $from, Carbon $to)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [
                $from->copy()->startOfDay(),
                $to->copy()->endOfDay(),
            ])
            ->where('orders.status', '!=', 'Cancelled')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.price * order_items.quantity) as gross_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('gross_revenue')
            ->get();
    }

    /**
     * Get the query builder for product performance metrics (with date filtering).
     */
    public function productPerformanceQuery(
        Carbon $from,
        Carbon $to,
        ?string $search = null,
        ?string $sortBy = null,
        ?string $sortDir = null
    ): \Illuminate\Database\Query\Builder {
        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->endOfDay();

        $query = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) use ($start, $end) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereBetween('orders.created_at', [$start, $end]);
            })
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('COALESCE(SUM(CASE WHEN orders.status != "Cancelled" THEN order_items.quantity ELSE 0 END), 0) as units_sold'),
                DB::raw('COALESCE(SUM(CASE WHEN orders.status != "Cancelled" THEN order_items.price * order_items.quantity ELSE 0 END), 0) as total_revenue'),
                DB::raw('COALESCE(SUM(CASE WHEN orders.status = "Cancelled" THEN order_items.quantity ELSE 0 END), 0) as cancelled_units'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders')
            )
            ->groupBy('products.id', 'products.name', 'products.sku');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortDir = strtolower($sortDir ?? '') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'units_sold' => 'units_sold',
            'cancelled_units' => 'cancelled_units',
            'total_revenue' => 'total_revenue',
            'total_orders' => 'total_orders',
        ];

        if ($sortBy && array_key_exists($sortBy, $allowedSorts)) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->orderByDesc('total_revenue');
        }

        return $query;
    }

    /**
     * Get product performance detailed details (paginated).
     */
    public function productPerformance(Carbon $from, Carbon $to, int $perPage = 25, ?string $search = null, ?string $sortBy = null, ?string $sortDir = null)
    {
        return $this->productPerformanceQuery($from, $to, $search, $sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get the query builder for customer behavior metrics (with date filtering).
     */
    public function customerBehaviorQuery(
        Carbon $from,
        Carbon $to,
        ?string $search = null,
        ?string $sortBy = null,
        ?string $sortDir = null
    ): \Illuminate\Database\Query\Builder {
        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->endOfDay();

        $query = DB::table('customers')
            ->leftJoin('orders', function ($join) use ($start, $end) {
                $join->on('customers.id', '=', 'orders.customer_id')
                     ->whereBetween('orders.created_at', [$start, $end]);
            })
            ->leftJoin('reviews', function ($join) use ($start, $end) {
                $join->on('customers.id', '=', 'reviews.customer_id')
                     ->whereBetween('reviews.created_at', [$start, $end]);
            })
            ->select(
                'customers.id',
                'customers.name',
                'customers.email',
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('COALESCE(SUM(orders.total), 0) as total_sales'),
                DB::raw('COUNT(DISTINCT reviews.id) as reviews_count')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.email');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'like', "%{$search}%")
                  ->orWhere('customers.email', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortDir = strtolower($sortDir ?? '') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'total_orders' => 'total_orders',
            'reviews_count' => 'reviews_count',
            'total_sales' => 'total_sales',
        ];

        if ($sortBy && array_key_exists($sortBy, $allowedSorts)) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->orderByDesc('total_sales');
        }

        return $query;
    }

    /**
     * Get customer behavior logs (paginated).
     */
    public function customerBehavior(Carbon $from, Carbon $to, int $perPage = 25, ?string $search = null, ?string $sortBy = null, ?string $sortDir = null)
    {
        return $this->customerBehaviorQuery($from, $to, $search, $sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get conversion conversion step metrics.
     */
    public function conversionFunnelMetrics(Carbon $from, Carbon $to): array
    {
        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->endOfDay();

        // 1. Total Sessions (unique session_ids in traffic_logs)
        $totalSessions = DB::table('traffic_logs')
            ->whereBetween('created_at', [$start, $end])
            ->distinct('session_id')
            ->count('session_id');

        // 2. Add to Cart Sessions (unique session_ids or customer_ids in carts)
        $addToCartSessions = DB::table('carts')
            ->whereBetween('created_at', [$start, $end])
            ->distinct()
            ->count(DB::raw('COALESCE(session_id, CAST(customer_id AS CHAR))'));

        // 3. Completed Purchase Sessions (unique session_ids or customer_ids in orders)
        $purchasedSessions = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->distinct()
            ->count(DB::raw('COALESCE(session_id, CAST(customer_id AS CHAR))'));

        // Cart abandonment metrics
        $totalCartsCreated = DB::table('carts')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // Abandoned carts (using the exact query method)
        $abandonedCarts = $this->abandonedCartsQuery($from, $to)->count();

        return [
            'sessions' => $totalSessions,
            'cart_adds' => $addToCartSessions,
            'purchases' => $purchasedSessions,
            'carts_created' => $totalCartsCreated,
            'carts_abandoned' => $abandonedCarts,
            'abandonment_rate' => $totalCartsCreated > 0 ? ($abandonedCarts / $totalCartsCreated) * 100 : 0.0,
            'conversion_rate' => $totalSessions > 0 ? ($purchasedSessions / $totalSessions) * 100 : 0.0,
        ];
    }

    /**
     * Query builder for abandoned carts list.
     */
    public function abandonedCartsQuery(
        Carbon $from,
        Carbon $to,
        ?string $search = null,
        ?string $sortBy = null,
        ?string $sortDir = null
    ): \Illuminate\Database\Query\Builder {
        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->endOfDay();

        $query = DB::table('carts')
            ->leftJoin('customers', 'carts.customer_id', '=', 'customers.id')
            ->whereBetween('carts.created_at', [$start, $end])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('orders')
                    ->where(function ($sub) {
                        $sub->whereRaw('orders.session_id = carts.session_id')
                            ->orWhere(function ($orSub) {
                                $orSub->whereNotNull('carts.customer_id')
                                      ->whereRaw('orders.customer_id = carts.customer_id');
                            });
                    });
            })
            ->select('carts.id', 'carts.session_id', 'customers.name as customer_name', 'carts.created_at',
                DB::raw('(SELECT SUM(quantity) FROM cart_items WHERE cart_items.cart_id = carts.id) as items_count')
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('carts.id', '=', $search)
                  ->orWhere('carts.session_id', 'like', "%{$search}%")
                  ->orWhere('customers.name', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortDir = strtolower($sortDir ?? '') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'items_count' => 'items_count',
            'created_at' => 'carts.created_at',
        ];

        if ($sortBy && array_key_exists($sortBy, $allowedSorts)) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->orderByDesc('carts.created_at');
        }

        return $query;
    }

    /**
     * Get traffic analysis page views & referrers.
     */
    public function trafficAnalysisMetrics(Carbon $from, Carbon $to): array
    {
        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->endOfDay();

        $pageViews = DB::table('traffic_logs')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $uniqueVisitors = DB::table('traffic_logs')
            ->whereBetween('created_at', [$start, $end])
            ->distinct('session_id')
            ->count('session_id');

        $topPages = DB::table('traffic_logs')
            ->whereBetween('created_at', [$start, $end])
            ->select('path', DB::raw('COUNT(*) as views'))
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $topReferrers = DB::table('traffic_logs')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('referrer')
            ->select('referrer', DB::raw('COUNT(*) as count'))
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'page_views'      => $pageViews,
            'unique_visitors' => $uniqueVisitors,
            'top_pages'       => $topPages,
            'top_referrers'   => $topReferrers,
        ];
    }
}

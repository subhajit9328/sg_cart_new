<?php

namespace SGCart\Reporting\Actions;

use Illuminate\Support\Carbon;
use SGCart\Reporting\Queries\CustomerReportQuery;
use SGCart\Reporting\Queries\OrderReportQuery;

class GetCustomerReportAction
{
    public function __construct(
        protected CustomerReportQuery $customerQuery,
        protected OrderReportQuery $orderQuery
    ) {}

    /**
     * Compute all customer report analytics.
     */
    public function execute(Carbon $from, Carbon $to, int $perPage = 25, ?string $search = null, ?string $sortBy = null, ?string $sortDir = null): array
    {
        $newCustomers = $this->customerQuery->newCustomersCount($from, $to);
        $activeCustomers = $this->customerQuery->activeCustomersCount($from, $to);
        $returningCustomers = $this->customerQuery->returningCustomersCount($from, $to);
        $totalOrders = $this->orderQuery->totalOrders($from, $to);

        $avgOrdersPerCustomer = $activeCustomers > 0 ? $totalOrders / $activeCustomers : 0.0;
        $avgCLV = $this->customerQuery->clvForActiveCustomers($from, $to);

        // Growth charts
        $dateSeries = $this->orderQuery->dateSeries($from, $to);
        $registrationsByDay = $this->customerQuery->customerGrowthSeries($from, $to);

        $chartGrowthLabels = [];
        $chartGrowthData   = [];
        foreach ($dateSeries as $date) {
            $chartGrowthLabels[] = count($dateSeries) <= 62
                ? Carbon::parse($date)->format('d M')
                : Carbon::parse($date)->format('M Y');
            $chartGrowthData[] = $registrationsByDay->get($date, 0);
        }

        // Top lists
        $topBySales = $this->customerQuery->topCustomersBySales($from, $to, 5);
        $topByOrders = $this->customerQuery->topCustomersByOrders($from, $to, 5);

        // Detailed table
        $customersDetailed = $this->customerQuery->customersDetailedQuery($from, $to, $perPage, $search, $sortBy, $sortDir);

        return [
            'date_from' => $from,
            'date_to' => $to,
            'days_in_range' => ceil(max(1, $from->diffInDays($to))),
            'metrics' => [
                'total_customers' => $this->customerQuery->totalRegisteredCustomersCount(),
                'new_customers' => $newCustomers,
                'active_customers' => $activeCustomers,
                'returning_customers' => $returningCustomers,
                'total_orders' => $totalOrders,
                'avg_orders' => $avgOrdersPerCustomer,
                'avg_clv' => $avgCLV,
            ],
            'chart_growth_labels' => array_unique($chartGrowthLabels),
            'chart_growth_data' => $chartGrowthData,
            'top_by_sales_labels' => $topBySales->pluck('name')->toArray(),
            'top_by_sales_data' => $topBySales->pluck('total_sales')->toArray(),
            'top_by_orders_labels' => $topByOrders->pluck('name')->toArray(),
            'top_by_orders_data' => $topByOrders->pluck('total_orders')->toArray(),
            'customers' => $customersDetailed,
        ];
    }
}

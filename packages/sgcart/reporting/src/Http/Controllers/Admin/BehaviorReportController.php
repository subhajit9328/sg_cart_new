<?php

namespace SGCart\Reporting\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use SGCart\Reporting\Queries\AdvancedReportsQuery;
use SGCart\Reporting\Http\Requests\OrderReportRequest;
use SGCart\Reporting\Http\Requests\ExportReportRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BehaviorReportController extends Controller
{
    public function __construct(
        protected AdvancedReportsQuery $advancedQuery
    ) {}

    public function index(OrderReportRequest $request)
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $perPage = config('reporting.per_page', 25);
        $behavior = $this->advancedQuery->customerBehavior($from, $to, $perPage);

        return view('reporting::admin.behavior.index', [
            'date_from' => $from,
            'date_to' => $to,
            'days_in_range' => max(1, $from->diffInDays($to) + 1),
            'behavior' => $behavior,
        ]);
    }

    public function export(ExportReportRequest $request): StreamedResponse
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $fileName = "customer-behavior-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return new StreamedResponse(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

            fputcsv($handle, ['Customer Name', 'Email', 'Total Orders', 'Total Spent in Period (₹)', 'Reviews Submitted']);

            $start = $from->copy()->startOfDay();
            $end   = $to->copy()->endOfDay();

            \Illuminate\Support\Facades\DB::table('customers')
                ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
                ->leftJoin('reviews', 'customers.id', '=', 'reviews.customer_id')
                ->select(
                    'customers.id',
                    'customers.name',
                    'customers.email',
                    \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                    \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN orders.created_at BETWEEN "' . $start . '" AND "' . $end . '" THEN orders.total ELSE 0 END) as total_sales'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT reviews.id) as reviews_count')
                )
                ->groupBy('customers.id', 'customers.name', 'customers.email')
                ->orderByDesc('total_sales')
                ->lazy()
                ->each(function ($row) use ($handle) {
                    fputcsv($handle, [
                        $row->name,
                        $row->email,
                        $row->total_orders,
                        number_format($row->total_sales, 2, '.', ''),
                        $row->reviews_count,
                    ]);
                });

            fclose($handle);
        }, 200, $headers);
    }
}

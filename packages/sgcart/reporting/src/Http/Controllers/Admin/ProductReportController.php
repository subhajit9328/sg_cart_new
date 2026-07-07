<?php

namespace SGCart\Reporting\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use SGCart\Reporting\Queries\AdvancedReportsQuery;
use SGCart\Reporting\Http\Requests\OrderReportRequest;
use SGCart\Reporting\Http\Requests\ExportReportRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductReportController extends Controller
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
        $performance = $this->advancedQuery->productPerformance(
            $from,
            $to,
            $perPage,
            $request->input('search'),
            $request->input('sort_by'),
            $request->input('sort_dir') ?? $request->input('sort_order')
        );

        return view('reporting::admin.products.index', [
            'date_from' => $from,
            'date_to' => $to,
            'days_in_range' => max(1, $from->diffInDays($to) + 1),
            'performance' => $performance,
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

        $fileName = "product-performance-{$from->format('Y-m-d')}-to-{$to->format('Y-m-d')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return new StreamedResponse(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

            fputcsv($handle, ['Product ID', 'Product Name', 'SKU', 'Units Sold', 'Total Revenue (₹)', 'Cancelled Units', 'Total Orders']);

            // Get non-paginated data using query builder
            $this->advancedQuery->productPerformanceQuery($from, $to)
                ->lazy()
                ->each(function ($row) use ($handle) {
                    fputcsv($handle, [
                        $row->id,
                        $row->name,
                        $row->sku ?? '—',
                        $row->units_sold,
                        number_format($row->total_revenue, 2, '.', ''),
                        $row->cancelled_units,
                        $row->total_orders,
                    ]);
                });

            fclose($handle);
        }, 200, $headers);
    }
}
